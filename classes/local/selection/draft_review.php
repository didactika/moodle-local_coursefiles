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
 * Reviewed batches for existing native form drafts.
 *
 * @package local_filebank
 * @copyright 2026 Didactika.org
 * @author Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_filebank\local\selection;

use local_filebank\local\catalog\material_source;
use local_filebank\local\migration\copy_decision;
use local_filebank\local\policy\author_policy;
use local_filebank\local\profile\profiles;

/**
 * Preflight existing draft contents and each authorized source leaf without writing files.
 */
final class draft_review {
    /**
     * Current.
     *
     * @param array $current Current.
     * @return array Result.
     */
    public static function current(array $current): array {
        $paths = [];
        $content = [];
        $count = count($current);
        $bytes = 0;
        $draftsnapshot = [];
        foreach ($current as $file) {
            $snapshot = copy_decision::snapshot($file);
            $draftsnapshot[] = $snapshot;
            $paths[$file->get_filepath() . $file->get_filename()] = $snapshot;
            $content[$file->get_contenthash()][] = $snapshot;
            $bytes += $file->get_filesize();
        }
        return compact('paths', 'content', 'count', 'bytes', 'draftsnapshot');
    }

    /**
     * Leaf.
     *
     * @param string $hash Hash.
     * @param array $context Context.
     * @return array Result.
     */
    public static function leaf(string $hash, array $context): array {
        [$file, $cm] = material_source::resolve($hash, $context['courseid'], $context['profileid']);
        author_policy::require_native_copy($file, (int)$cm->course, $context['courseid']);
        $source = copy_decision::snapshot($file);
        $path = destination_path::make($context['basepath'], $context['relativepath']);
        $filename = $context['rename'] ?? $file->get_filename();
        if (!is_string($filename)) {
            throw new \moodle_exception('invalidselection', 'local_filebank');
        }
        $filename = selection_options::filename($filename);
        $key = $path . $filename;
        $decision = copy_decision::classify($source, $context['paths'][$key] ?? null);
        $decision = self::duplicates($file, $source, $context['content'], $decision, $context['skipcontent']);
        draft_limits::leaf($file, $context['limits'], $path, $filename);
        return ['pathnamehash' => $hash, 'filepath' => $path, 'filename' => $filename,
            'decision' => $decision, 'snapshot' => $source, 'contenthash' => $file->get_contenthash(),
            'filesize' => $file->get_filesize()];
    }

    /**
     * Duplicates.
     *
     * @param \stored_file $file File.
     * @param array $source Source.
     * @param array $content Content.
     * @param string $decision Decision.
     * @param bool $skipcontent Skipcontent.
     * @return string Result.
     */
    private static function duplicates(
        \stored_file $file,
        array $source,
        array $content,
        string $decision,
        bool $skipcontent
    ): string {
        if ($decision === 'copy' && $skipcontent) {
            foreach ($content[$file->get_contenthash()] ?? [] as $existing) {
                $decision = copy_decision::classify($source, $existing) === 'skip_identical'
                    ? 'skip_content' : 'blocked_attribution';
                if ($decision === 'blocked_attribution') {
                    break;
                }
            }
        }
        return $decision;
    }
}
