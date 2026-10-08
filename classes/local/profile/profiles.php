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
 * Immutable rule revisions.
 *
 * @package local_coursebridge
 * @copyright 2026 CourseBridge contributors
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_coursebridge\local\profile;

use local_coursebridge\local\logging\log;

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
        require_capability('local/coursebridge:manageprofiles', \context_system::instance());
        try {
            $config = json_decode($json, true, 64, JSON_THROW_ON_ERROR);
        } catch (\JsonException $exception) {
            throw new \moodle_exception('invalidprofile', 'local_coursebridge');
        }
        new profile_config($config);
        $lock = \core\lock\lock_config::get_lock_factory('local_coursebridge')->get_lock('profile:' . $profileid, 10);
        if (!$lock) {
            throw new \moodle_exception('lockfailed', 'local_coursebridge');
        }
        try {
            $transaction = $DB->start_delegated_transaction();
            if (!$profileid) {
                $profileid = $DB->insert_record('local_coursebridge', (object)[
                    'uuid' => \core\uuid::generate(), 'name' => $config['name'] ?? 'CourseBridge',
                    'activeid' => 0, 'enabled' => 1,
                ]);
            }
            $profile = $DB->get_record('local_coursebridge', ['id' => $profileid], '*', MUST_EXIST);
            $number = 1 + (int)$DB->get_field_sql(
                'SELECT MAX(revision) FROM {local_coursebridge_revision} WHERE profileid = ?',
                [$profileid]
            );
            $canonical = json_encode($config, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            $profile->activeid = $DB->insert_record('local_coursebridge_revision', (object)[
                'profileid' => $profileid, 'revision' => $number, 'configjson' => $canonical,
                'digest' => hash('sha256', $canonical), 'userid' => $USER->id, 'timecreated' => time(),
            ]);
            $profile->name = $config['name'] ?? $profile->name;
            $DB->update_record('local_coursebridge', $profile);
            $transaction->allow_commit();
            log::record('profile_activated', $profile->uuid, ['revision' => $number]);
            return $profileid;
        } finally {
            $lock->release();
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
        $profile = $DB->get_record('local_coursebridge', ['id' => $profileid, 'enabled' => 1], '*', MUST_EXIST);
        $revision = $DB->get_record('local_coursebridge_revision', ['id' => $profile->activeid], '*', MUST_EXIST);
        return [$profile, $revision, new profile_config(json_decode($revision->configjson, true))];
    }
}
