<?php
// This file is part of Moodle - https://moodle.org/
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
 * Immutable rule revisions.
 *
 * @package local_filebank
 * @copyright 2026 Didactika.org
 * @author Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_filebank\local\profile;

use local_filebank\local\logging\log;

/**
 * Owns validated configuration persistence.
 */
final class profiles {
    /**
     * Save and activate a revision.
     *
     * @param string $json Document.
     * @param int $profileid Existing profile or zero.
     * @return int Profile.
     */
    public static function save(string $json, int $profileid = 0): int {

        global $DB, $USER;
        require_capability('local/filebank:manageprofiles', \context_system::instance());
        try {
            $config = json_decode($json, true, 64, JSON_THROW_ON_ERROR);
        } catch (\JsonException $exception) {
            throw new \moodle_exception('invalidprofile', 'local_filebank');
        }
        new profile_config($config);
        $lock = \core\lock\lock_config::get_lock_factory('local_filebank')->get_lock('profile:' . $profileid, 10);
        if (!$lock) {
            throw new \moodle_exception('lockfailed', 'local_filebank');
        }
        try {
            $transaction = $DB->start_delegated_transaction();
            if (!$profileid) {
                $profileid = $DB->insert_record(
                    'local_filebank',
                    (object)[
                        'uuid' => \core\uuid::generate(), 'name' => $config['name'] ?? 'FileBank',
                        'activeid' => 0, 'enabled' => 1,
                    ]
                );
            }
            $profile = $DB->get_record('local_filebank', ['id' => $profileid], '*', MUST_EXIST);
            if ($profile->deleted) {
                throw new \moodle_exception('invalidprofile', 'local_filebank');
            }
            $previousid = (int)$profile->activeid;
            $previous = $previousid ? $DB->get_record('local_filebank_revision', ['id' => $previousid]) : false;
            $number = 1 + (int)$DB->get_field_sql(
                'SELECT MAX(revision) FROM {local_filebank_revision} WHERE profileid = ?',
                [$profileid]
            );
            $canonical = json_encode($config, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            $profile->activeid = $DB->insert_record(
                'local_filebank_revision',
                (object)[
                    'profileid' => $profileid, 'revision' => $number, 'configjson' => $canonical,
                    'digest' => hash('sha256', $canonical), 'userid' => $USER->id, 'timecreated' => time(),
                ]
            );
            $profile->name = $config['name'] ?? $profile->name;
            $DB->update_record('local_filebank', $profile);
            self::reuse_catalog($profile, $previous, $config);
            \local_filebank\task\refresh_profile::queue($profileid, (int)$profile->activeid);
            $transaction->allow_commit();
            log::record('profile_activated', $profile->uuid, ['revision' => $number]);
            return $profileid;
        } finally {
            $lock->release();
        }
    }

    /**
     * Enable, disable or remove selected profiles, retaining referenced revisions.
     *
     * @param array $ids Profile IDs.
     * @param string $action enable, disable or delete.
     */
    public static function change_state(array $ids, string $action): void {

        global $DB;
        require_capability('local/filebank:manageprofiles', \context_system::instance());
        $ids = array_values(array_unique(array_map('intval', $ids)));
        sort($ids);
        if (!$ids || count($ids) > 500 || !in_array($action, ['enable', 'disable', 'delete'], true)) {
            throw new \moodle_exception('selectprofiles', 'local_filebank');
        }
        $locks = [];
        try {
            foreach ($ids as $id) {
                $lock = \core\lock\lock_config::get_lock_factory('local_filebank')->get_lock('profile:' . $id, 10);
                if (!$lock) {
                    throw new \moodle_exception('lockfailed', 'local_filebank');
                }
                $locks[] = $lock;
            }
            $transaction = $DB->start_delegated_transaction();
            foreach ($ids as $id) {
                self::update_state($id, $action);
            }
            $transaction->allow_commit();
        } finally {
            foreach (array_reverse($locks) as $lock) {
                $lock->release();
            }
        }
    }

    /**
     * Load an exact enabled revision.
     *
     * @param int $profileid Profile.
     * @return array Profile, revision and rules.
     */
    public static function load(int $profileid): array {
        global $DB;
        $profile = $DB->get_record('local_filebank', ['id' => $profileid, 'enabled' => 1, 'deleted' => 0], '*', MUST_EXIST);
        $revision = $DB->get_record('local_filebank_revision', ['id' => $profile->activeid], '*', MUST_EXIST);
        return [$profile, $revision, new profile_config(json_decode($revision->configjson, true))];
    }

    /**
     * Reuse catalog.
     *
     * @param \stdClass $profile Profile.
     * @param \stdClass|false $previous Previously active version, if any.
     * @param array $config Config.
     */
    private static function reuse_catalog(\stdClass $profile, $previous, array $config): void {
        global $DB;
        if ($previous) {
            $oldconfig = json_decode($previous->configjson, true);
            if (
                $oldconfig['relationship'] === $config['relationship']
                    && ($oldconfig['scope'] ?? []) === ($config['scope'] ?? [])
            ) {
                // Reuse matching metadata for presentation/publication edits; access is still checked live.
                foreach (['local_filebank_course', 'local_filebank_value'] as $table) {
                    $DB->set_field(
                        $table,
                        'revisionid',
                        $profile->activeid,
                        ['profileid' => $profile->id, 'revisionid' => $previous->id]
                    );
                }
            }
        }
    }

    /**
     * Update state.
     *
     * @param int $id Id.
     * @param string $action Action.
     */
    private static function update_state(int $id, string $action): void {
        global $DB;
        $profile = $DB->get_record('local_filebank', ['id' => $id, 'deleted' => 0], '*', MUST_EXIST);
        if (
            $DB->record_exists_sql(
                'SELECT r.id FROM {local_filebank_run} r
                JOIN {local_filebank_plan} p ON p.id = r.planid
                WHERE p.profileid = ? AND (r.state = ? OR r.state = ?)',
                [$id, 'queued', 'running']
            )
        ) {
            throw new \moodle_exception('profileinuse', 'local_filebank');
        }
        $profile->enabled = $action === 'enable' ? 1 : 0;
        $profile->deleted = $action === 'delete' ? 1 : 0;
        $DB->update_record('local_filebank', $profile);
        if ($action === 'enable') {
            \local_filebank\task\refresh_profile::queue((int)$profile->id, (int)$profile->activeid);
        }
        log::record('profile_state_changed', $profile->uuid, ['action' => $action]);
    }
}
