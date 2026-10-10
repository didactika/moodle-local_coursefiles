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
 * Excludes existing native destination content from interactive selection.
 *
 * @package local_coursefiles
 * @copyright 2026 Didactika.org
 * @author Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_coursefiles\local\selection;

/**
 * Byte identity is checked against this actor's live draft, independently of names and paths.
 */
final class destination_content {
    /**
     * Build an indexed destination anti-join before catalog pagination or recursive expansion.
     *
     * @param string $alias Internal source table alias.
     * @param int $draftid Actor-owned native draft, or zero for ordinary catalog browsing.
     * @return array SQL condition and named parameters.
     */
    public static function exclusion(string $alias, int $draftid): array {
        global $USER;
        if ($draftid <= 0) {
            return ['', []];
        }
        if (!preg_match('/^[a-z][a-z0-9_]*$/', $alias)) {
            throw new \coding_exception('Invalid internal file alias');
        }
        return [' AND NOT EXISTS (SELECT 1 FROM {files} destination WHERE destination.contextid = :destinationcontext'
            . ' AND destination.component = :destinationcomponent AND destination.filearea = :destinationarea'
            . ' AND destination.itemid = :destinationdraft AND destination.filename <> :destinationdirectory'
            . ' AND destination.contenthash = ' . $alias . '.contenthash'
            . ' AND destination.filesize = ' . $alias . '.filesize)',
            ['destinationcontext' => \context_user::instance($USER->id)->id, 'destinationcomponent' => 'user',
                'destinationarea' => 'draft', 'destinationdraft' => $draftid, 'destinationdirectory' => '.']];
    }

    /**
     * Remove existing content from one bounded set with one query, preserving source authorization.
     *
     * @param array $files Authorized native files keyed by pathname hash.
     * @param int $draftid Actor-owned destination draft.
     * @return array Remaining native files with their original keys.
     */
    public static function filter(array $files, int $draftid): array {
        global $DB;
        if (!$files || $draftid <= 0) {
            return $files;
        }
        [$in, $params] = $DB->get_in_or_equal(array_keys($files), SQL_PARAMS_NAMED, 'sourcehash');
        [$exclusion, $destinationparams] = self::exclusion('sourcefile', $draftid);
        $remaining = $DB->get_records_sql('SELECT sourcefile.pathnamehash FROM {files} sourcefile'
            . ' WHERE sourcefile.pathnamehash ' . $in . $exclusion, $params + $destinationparams);
        return array_intersect_key($files, $remaining);
    }
}
