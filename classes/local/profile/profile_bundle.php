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
    public const MAX_BYTES = 16777216;

    /**
     * Export selected profiles and their retained configuration versions.
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
        $where = ' WHERE p.deleted = 0';
        if (!$all) {
            [$condition, $params] = $DB->get_in_or_equal($ids, SQL_PARAMS_NAMED);
            $where .= ' AND p.id ' . $condition;
        }
        $records = $DB->get_recordset_sql('SELECT p.id, p.activeid, p.enabled, r.configjson FROM {local_multipicker} p
            JOIN {local_multipicker_revision} r ON r.id = p.activeid' . $where . ' ORDER BY p.name, p.id', $params);
        $documents = [];
        foreach ($records as $record) {
            $versions = [];
            $active = 0;
            foreach ($DB->get_records('local_multipicker_revision', ['profileid' => $record->id], 'revision') as $version) {
                $versions[] = ['version' => (int)$version->revision, 'created' => (int)$version->timecreated,
                    'config' => json_decode($version->configjson, true)];
                if ($version->id == $record->activeid) {
                    $active = (int)$version->revision;
                }
            }
            $documents[] = ['active' => $active, 'enabled' => (bool)$record->enabled, 'versions' => $versions];
        }
        $records->close();
        if (!$all && count($documents) !== count($ids)) {
            throw new \moodle_exception('invalidprofile', 'local_multipicker');
        }
        $json = json_encode(
            ['format' => 'multipicker-profiles', 'version' => 2, 'profiles' => $documents],
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
        );
        self::documents($json);
        return $json;
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
            if ($document['format'] !== 'multipicker-profiles' || !in_array($document['version'] ?? null, [1, 2], true)) {
                throw new \moodle_exception('invalidprofilebundle', 'local_multipicker');
            }
            $documents = $document['profiles'] ?? [];
        } else {
            $documents = [$document];
        }
        if (!is_array($documents) || !array_is_list($documents) || !$documents || count($documents) > 500) {
            throw new \moodle_exception('invalidprofilebundle', 'local_multipicker');
        }
        if (($document['version'] ?? 1) === 2 && isset($document['format'])) {
            $configs = [];
            $total = 0;
            foreach ($documents as $entry) {
                if (
                    !is_array($entry) || !is_int($entry['active'] ?? null) || !is_bool($entry['enabled'] ?? null)
                        || !is_array($entry['versions'] ?? null) || !array_is_list($entry['versions']) || !$entry['versions']
                ) {
                    throw new \moodle_exception('invalidprofilebundle', 'local_multipicker');
                }
                $last = 0;
                $active = null;
                foreach ($entry['versions'] as $version) {
                    if (
                        !is_array($version) || !is_int($version['version'] ?? null) || $version['version'] <= $last
                            || $version['version'] >= 2147483647
                            || !is_int($version['created'] ?? null) || $version['created'] < 0
                            || !is_array($version['config'] ?? null) || ++$total > 5000
                    ) {
                        throw new \moodle_exception('invalidprofilebundle', 'local_multipicker');
                    }
                    if (
                        !is_string($version['config']['name'] ?? null) || trim($version['config']['name']) === ''
                            || \core_text::strlen($version['config']['name']) > 255
                    ) {
                        throw new \moodle_exception('invalidprofilebundle', 'local_multipicker');
                    }
                    new profile_config($version['config']);
                    $last = $version['version'];
                    if ($last === $entry['active']) {
                        $active = $version['config'];
                    }
                }
                if ($active === null) {
                    throw new \moodle_exception('invalidprofilebundle', 'local_multipicker');
                }
                $configs[] = $active;
            }
            $documents = $configs;
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
        global $DB, $USER;
        require_capability('local/multipicker:manageprofiles', \context_system::instance());
        $documents = self::documents($json);
        $transaction = $DB->start_delegated_transaction();
        $ids = [];
        $bundle = json_decode($json, true);
        foreach ($documents as $config) {
            $id = profiles::save(json_encode($config));
            $ids[] = $id;
            if (($bundle['format'] ?? '') === 'multipicker-profiles' && ($bundle['version'] ?? 1) === 2) {
                $entry = $bundle['profiles'][count($ids) - 1];
                $initial = $DB->get_field('local_multipicker', 'activeid', ['id' => $id]);
                $activeid = 0;
                foreach ($entry['versions'] as $version) {
                    $canonical = json_encode($version['config'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
                    $versionid = $DB->insert_record('local_multipicker_revision', (object)[
                        'profileid' => $id, 'revision' => $version['version'] + 1, 'configjson' => $canonical,
                        'digest' => hash('sha256', $canonical), 'userid' => $USER->id,
                        'timecreated' => $version['created'],
                    ]);
                    if ($version['version'] === $entry['active']) {
                        $activeid = $versionid;
                    }
                }
                $DB->update_record('local_multipicker', (object)[
                    'id' => $id, 'activeid' => $activeid, 'enabled' => $entry['enabled'],
                ]);
                $DB->delete_records('local_multipicker_revision', ['id' => $initial]);
                foreach ($DB->get_records('local_multipicker_revision', ['profileid' => $id], 'revision') as $version) {
                    $DB->set_field('local_multipicker_revision', 'revision', $version->revision - 1, ['id' => $version->id]);
                }
                \local_multipicker\task\refresh_profile::queue($id, $activeid);
            }
        }
        $transaction->allow_commit();
        return $ids;
    }
}
