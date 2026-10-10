<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Profile version comparison, restoration and retention.
 * @package local_multipicker
 * @copyright 2026 MultiPicker contributors
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_multipicker\local\profile;

/**
 * Owns historical configurations under one profile identity.
 */
final class profile_history {
    /**
     * Activate an existing version without changing its content, author or date.
     * @param int $profileid Profile.
     * @param int $versionid Historical record belonging to that profile.
     * @return int Unchanged profile identity.
     */
    public static function restore(int $profileid, int $versionid): int {
        global $DB;
        require_capability('local/multipicker:manageprofiles', \context_system::instance());
        $lock = \core\lock\lock_config::get_lock_factory('local_multipicker')->get_lock('profile:' . $profileid, 10);
        if (!$lock) {
            throw new \moodle_exception('lockfailed', 'local_multipicker');
        }
        try {
            $transaction = $DB->start_delegated_transaction();
            $profile = $DB->get_record('local_multipicker', ['id' => $profileid, 'deleted' => 0], '*', MUST_EXIST);
            $version = $DB->get_record(
                'local_multipicker_revision',
                ['id' => $versionid, 'profileid' => $profileid],
                '*',
                MUST_EXIST
            );
            $config = json_decode($version->configjson, true);
            new profile_config($config);
            if ((int)$profile->activeid !== $versionid) {
                $previousid = (int)$profile->activeid;
                $previous = $DB->get_record('local_multipicker_revision', ['id' => $previousid], '*', MUST_EXIST);
                $oldconfig = json_decode($previous->configjson, true);
                $profile->activeid = $versionid;
                $profile->name = $config['name'];
                $DB->update_record('local_multipicker', $profile);
                if (
                    $oldconfig['relationship'] === $config['relationship']
                        && ($oldconfig['scope'] ?? []) === ($config['scope'] ?? [])
                ) {
                    foreach (['local_multipicker_course', 'local_multipicker_value'] as $table) {
                        $DB->set_field(
                            $table,
                            'revisionid',
                            $versionid,
                            ['profileid' => $profileid, 'revisionid' => $previousid]
                        );
                    }
                }
                // Rebuild derived catalog metadata; historical migration snapshots remain unchanged.
                \local_multipicker\task\refresh_profile::queue($profileid, $versionid);
                \local_multipicker\local\logging\log::record(
                    'profile_activated',
                    $profile->uuid,
                    ['revision' => $version->revision, 'restored' => true]
                );
            }
            $transaction->allow_commit();
            return $profileid;
        } finally {
            $lock->release();
        }
    }

    /**
     * Compare configuration fields, retaining added and removed values.
     * @param array $before Older configuration.
     * @param array $after Newer configuration.
     * @return array Escapable side-by-side rows.
     */
    public static function compare(array $before, array $after): array {
        $flatten = static function (array $config) use (&$flatten): array {
            $result = [];
            foreach ($config as $key => $value) {
                if (is_array($value) && $value) {
                    foreach ($flatten($value) as $path => $leaf) {
                        $result[$key . '.' . $path] = $leaf;
                    }
                } else {
                    $result[$key] = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                }
            }
            return $result;
        };
        $left = $flatten($before);
        $right = $flatten($after);
        $keys = array_unique(array_merge(array_keys($left), array_keys($right)));
        sort($keys);
        $rows = [];
        foreach ($keys as $key) {
            $rows[] = ['field' => $key, 'before' => $left[$key] ?? '', 'after' => $right[$key] ?? '',
                'changed' => ($left[$key] ?? null) !== ($right[$key] ?? null)];
        }
        return $rows;
    }

    /**
     * Remove a bounded set of expired versions, preserving active and migration references.
     * @param int $now Reference timestamp.
     * @return int Removed versions.
     */
    public static function clean(int $now): int {
        global $DB;
        $days = (int)get_config('local_multipicker', 'versionretentiondays');
        $max = (int)get_config('local_multipicker', 'versionretentionmax');
        if (!$days && !$max) {
            return 0;
        }
        $expired = [];
        $params = [];
        if ($days > 0) {
            $expired[] = 'r.timecreated < ?';
            $params[] = $now - $days * DAYSECS;
        }
        if ($max > 0) {
            $expired[] = '(SELECT COUNT(1) FROM {local_multipicker_revision} n
                WHERE n.profileid = r.profileid AND n.revision > r.revision) >= ?';
            $params[] = $max;
        }
        $records = $DB->get_records_sql('SELECT r.id, r.profileid FROM {local_multipicker_revision} r
            JOIN {local_multipicker} p ON p.id = r.profileid WHERE p.activeid <> r.id
            AND NOT EXISTS (SELECT 1 FROM {local_multipicker_plan} m WHERE m.revisionid = r.id)
            AND (' . implode(' OR ', $expired) . ') ORDER BY r.timecreated, r.id', $params, 0, 100);
        $count = 0;
        foreach ($records as $record) {
            $lock = \core\lock\lock_config::get_lock_factory('local_multipicker')->get_lock('profile:' . $record->profileid, 0);
            if (!$lock) {
                continue;
            }
            try {
                $transaction = $DB->start_delegated_transaction();
                if (
                    !$DB->record_exists('local_multipicker', ['activeid' => $record->id])
                        && !$DB->record_exists('local_multipicker_plan', ['revisionid' => $record->id])
                ) {
                    foreach (['local_multipicker_value', 'local_multipicker_course', 'local_multipicker_revision'] as $table) {
                        $DB->delete_records($table, [$table === 'local_multipicker_revision' ? 'id' : 'revisionid' => $record->id]);
                    }
                    $count++;
                }
                $transaction->allow_commit();
            } finally {
                $lock->release();
            }
        }
        return $count;
    }
}
