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
 * Portable profile configuration without site records or material data.
 *
 * @package local_multipicker
 * @copyright 2026 MultiPicker contributors
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_multipicker\local\profile;

/**
 * Validates complete bundles before importing independent new profiles.
 */
final class profile_bundle {
    /** @var int Maximum uploaded bundle size. */
    public const MAX_BYTES = 1048576;

    /**
     * Export the active configuration of selected profiles, or all profiles.
     *
     * @param array $ids Explicit IDs.
     * @param bool $all Export every profile.
     * @return string Portable JSON.
     */
    public static function export(array $ids, bool $all = false): string {
        global $DB;
        require_capability('local/multipicker:manageprofiles', \context_system::instance());
        $ids = array_values(array_unique(array_map('intval', $ids)));
        if (!$all && !$ids) {
            throw new \moodle_exception('selectprofiles', 'local_multipicker');
        }
        $params = [];
        $where = '';
        if (!$all) {
            [$condition, $params] = $DB->get_in_or_equal($ids, SQL_PARAMS_NAMED);
            $where = ' WHERE p.id ' . $condition;
        }
        $records = $DB->get_recordset_sql('SELECT p.id, r.configjson FROM {local_multipicker} p
            JOIN {local_multipicker_revision} r ON r.id = p.activeid' . $where . ' ORDER BY p.name, p.id', $params);
        $documents = [];
        foreach ($records as $record) {
            $documents[] = json_decode($record->configjson, true);
        }
        $records->close();
        if (!$all && count($documents) !== count($ids)) {
            throw new \moodle_exception('invalidprofile', 'local_multipicker');
        }
        return json_encode(
            ['format' => 'multipicker-profiles', 'version' => 1, 'profiles' => $documents],
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
        );
    }

    /**
     * Validate a bundle or a single existing configuration document.
     *
     * @param string $json Uploaded JSON.
     * @return array Complete validated documents.
     */
    public static function documents(string $json): array {
        if (strlen($json) > self::MAX_BYTES) {
            throw new \moodle_exception('invalidprofilebundle', 'local_multipicker');
        }
        try {
            $document = json_decode($json, true, 64, JSON_THROW_ON_ERROR);
        } catch (\JsonException $exception) {
            throw new \moodle_exception('invalidprofilebundle', 'local_multipicker');
        }
        if (!is_array($document)) {
            throw new \moodle_exception('invalidprofilebundle', 'local_multipicker');
        }
        if (isset($document['format'])) {
            if ($document['format'] !== 'multipicker-profiles' || ($document['version'] ?? null) !== 1) {
                throw new \moodle_exception('invalidprofilebundle', 'local_multipicker');
            }
            $documents = $document['profiles'] ?? [];
        } else {
            $documents = [$document];
        }
        if (!is_array($documents) || !array_is_list($documents) || !$documents || count($documents) > 500) {
            throw new \moodle_exception('invalidprofilebundle', 'local_multipicker');
        }
        foreach ($documents as $config) {
            if (
                !is_array($config) || !is_string($config['name'] ?? null) || trim($config['name']) === ''
                    || \core_text::strlen($config['name']) > 255
            ) {
                throw new \moodle_exception('invalidprofilebundle', 'local_multipicker');
            }
            new profile_config($config);
        }
        return $documents;
    }

    /**
     * Import all validated documents atomically as new profiles.
     *
     * @param string $json Portable bundle.
     * @return array New profile IDs.
     */
    public static function import(string $json): array {
        global $DB;
        require_capability('local/multipicker:manageprofiles', \context_system::instance());
        $documents = self::documents($json);
        $transaction = $DB->start_delegated_transaction();
        $ids = [];
        foreach ($documents as $config) {
            $ids[] = profiles::save(json_encode($config));
        }
        $transaction->allow_commit();
        return $ids;
    }
}
