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
 * Deterministic content and attribution conflict decisions.
 *
 * @package local_coursefiles
 * @copyright 2026 Didactika.org
 * @author Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_coursefiles\local\migration;

/**
 * Distinguishes byte identity from attribution and destination identity.
 */
final class copy_decision {
    /**
     * Classify a destination conflict.
     *
     * @param array $source Content/author/license snapshot.
     * @param array|null $target Existing same-path snapshot.
     * @return string Decision.
     */
    public static function classify(array $source, ?array $target): string {
        if (!$target) {
            return 'copy';
        }
        if ($source['contenthash'] !== $target['contenthash'] || $source['filesize'] !== $target['filesize']) {
            return 'blocked_content';
        }
        return $source['author'] === $target['author'] && $source['license'] === $target['license']
            ? 'skip_identical' : 'blocked_attribution';
    }

    /**
     * Capture current native file identity and metadata.
     *
     * @param \stored_file $file File.
     * @return array Snapshot.
     */
    public static function snapshot(\stored_file $file): array {
        return ['pathnamehash' => $file->get_pathnamehash(), 'contenthash' => $file->get_contenthash(),
            'filesize' => $file->get_filesize(), 'filepath' => $file->get_filepath(),
            'filename' => $file->get_filename(), 'author' => $file->get_author(), 'license' => $file->get_license()];
    }
}
