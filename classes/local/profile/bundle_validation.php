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
 * Portable profile configuration without site records or material data.
 *
 * @package local_filebank
 * @copyright 2026 Didactika.org
 * @author Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_filebank\local\profile;

use local_filebank\local\compatibility;

/**
 * Validate portable documents and complete bounded version histories before writes.
 */
final class bundle_validation {
    /**
     * Decode.
     *
     * @param string $json Uploaded portable document.
     * @return array Result.
     */
    public static function decode(string $json): array {

        if (strlen($json) > profile_bundle::MAX_BYTES) {
            throw new \moodle_exception('invalidprofilebundle', 'local_filebank');
        }
        try {
            $document = json_decode($json, true, 64, JSON_THROW_ON_ERROR);
        } catch (\JsonException $exception) {
            throw new \moodle_exception('invalidprofilebundle', 'local_filebank');
        }
        if (!is_array($document)) {
            throw new \moodle_exception('invalidprofilebundle', 'local_filebank');
        }
        if (isset($document['format'])) {
            if (
                !in_array($document['format'], ['filebank-profiles', 'multipicker-profiles'], true)
                    || !in_array($document['version'] ?? null, [1, 2], true)
            ) {
                throw new \moodle_exception('invalidprofilebundle', 'local_filebank');
            }
            $documents = $document['profiles'] ?? [];
        } else {
            $documents = [$document];
        }
        self::profile_list($documents);
        return [$document, $documents];
    }

    /**
     * Versions.
     *
     * @param array $documents Profile version histories.
     * @return array Result.
     */
    public static function versions(array $documents): array {
        $configs = [];
        $total = 0;
        foreach ($documents as $entry) {
            self::entry($entry);
            $last = 0;
            $active = null;
            foreach ($entry['versions'] as $version) {
                self::version($version, $last, $total);
                $last = $version['version'];
                if ($last === $entry['active']) {
                    $active = $version['config'];
                }
            }
            if ($active === null) {
                throw new \moodle_exception('invalidprofilebundle', 'local_filebank');
            }
            $configs[] = $active;
        }
        return $configs;
    }

    /**
     * Entry.
     *
     * @param mixed $entry One untrusted history entry.
     */
    private static function entry($entry): void {
        if (
            !is_array($entry) || !is_int($entry['active'] ?? null) || !is_bool($entry['enabled'] ?? null)
                || !is_array($entry['versions'] ?? null) || !compatibility::is_list($entry['versions'])
                || !$entry['versions']
        ) {
            throw new \moodle_exception('invalidprofilebundle', 'local_filebank');
        }
    }

    /**
     * Version.
     *
     * @param mixed $version One untrusted version.
     * @param int $last Previous version number.
     * @param int $total Running version count.
     */
    private static function version($version, int $last, int &$total): void {
        if (
            !is_array($version) || !is_int($version['version'] ?? null) || $version['version'] <= $last
                || $version['version'] >= 2147483647
                || !is_array($version['config'] ?? null) || ++$total > 5000
        ) {
            throw new \moodle_exception('invalidprofilebundle', 'local_filebank');
        }
        if (!is_int($version['created'] ?? null) || $version['created'] < 0) {
            throw new \moodle_exception('invalidprofilebundle', 'local_filebank');
        }
        self::configuration($version['config']);
    }

    /**
     * Configuration.
     *
     * @param mixed $config One untrusted profile configuration.
     */
    public static function configuration($config): void {
        if (
            !is_array($config) || !is_string($config['name'] ?? null) || trim($config['name']) === ''
                || \core_text::strlen($config['name']) > 255
        ) {
            throw new \moodle_exception('invalidprofilebundle', 'local_filebank');
        }
        new profile_config($config);
    }

    /**
     * Profile list.
     *
     * @param mixed $documents Untrusted profile list.
     */
    private static function profile_list($documents): void {
        if (!is_array($documents) || !compatibility::is_list($documents) || !$documents || count($documents) > 500) {
            throw new \moodle_exception('invalidprofilebundle', 'local_filebank');
        }
    }
}
