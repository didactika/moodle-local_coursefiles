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
 * Internal service: limits must come from the owning server-side form, never a browser request.
 */
final class draft_import {
    /**
     * Review the current selection and draft without writing files or attribution.
     *
     * @param int $courseid Destination course.
     * @param int $profileid Enabled profile.
     * @param int $draftid Existing current-user draft.
     * @param array $items Source selectors.
     * @param array $limits Owning form's authoritative options.
     * @param string $basepath Current draft destination directory.
     * @param string $mode Folder placement mode.
     * @param array $options Reviewed mapping roots and destination filenames.
     * @return array Decisions, resulting file count/size and eligibility.
     */
    public static function review(
        int $courseid,
        int $profileid,
        int $draftid,
        array $items,
        array $limits,
        string $basepath = '/',
        string $mode = 'paths',
        array $options = []
    ): array {

        global $USER;
        destination_access::require($courseid);
        draft_limits::contract($limits);
        $basepath = material_selection::path($basepath);
        $fs = get_file_storage();
        $context = \context_user::instance($USER->id);
        if ($draftid <= 0 || !$fs->get_file($context->id, 'user', 'draft', $draftid, $basepath, '.')) {
            throw new \moodle_exception('invaliddraft', 'local_filebank');
        }
        $current = $fs->get_area_files($context->id, 'user', 'draft', $draftid, 'id', false);
        $draft = draft_review::current($current);
        $paths = $draft['paths'];
        $content = $draft['content'];
        $count = $draft['count'];
        $bytes = $draft['bytes'];
        $draftsnapshot = $draft['draftsnapshot'];
        [, $revision, $rules] = profiles::load($profileid);
        $contentpolicy = $rules->get_config()['migration']['same_content_different_path'] ?? 'skip-and-report';
        $skipcontent = $contentpolicy === 'skip-and-report';
        $operations = [];
        $blocked = false;
        $files = material_selection::resolve($courseid, $profileid, $items);
        draft_limits::selection($files, $limits);
        $mapping = material_selection::map_paths(
            $files,
            array_merge($items, selection_options::parse($options['roots'] ?? [])),
            $mode
        );
        $renames = $options['renames'] ?? [];
        draft_limits::renames($files, $renames);
        foreach (array_keys($files) as $hash) {
            $context = compact('courseid', 'profileid', 'basepath', 'paths', 'content', 'limits', 'skipcontent');
            $context['relativepath'] = $mapping[$hash];
            $context['rename'] = $renames[$hash] ?? null;
            $operation = draft_review::leaf($hash, $context);
            $decision = $operation['decision'];
            $source = $operation['snapshot'];
            $key = $operation['filepath'] . $operation['filename'];
            if ($decision === 'copy') {
                $count++;
                $bytes += $operation['filesize'];
                $paths[$key] = $source;
                $content[$operation['contenthash']][] = $source;
            }
            $blocked = $blocked || str_starts_with($decision, 'blocked_');
            unset($operation['contenthash'], $operation['filesize']);
            $operations[] = $operation;
        }
        draft_limits::total($limits, $count, $bytes);
        $digest = hash(
            'sha256',
            json_encode(
                [
                    $USER->id, $courseid, $profileid, $revision->id, $draftid, $basepath, $mode, $limits,
                    $draftsnapshot, $operations,
                ],
                JSON_THROW_ON_ERROR
            )
        );
        return ['operations' => $operations, 'filecount' => $count, 'bytes' => $bytes,
            'blocked' => $blocked, 'digest' => $digest];
    }

    /**
     * Revalidate and copy one bounded batch into the existing draft, preserving existing content.
     *
     * @param int $courseid Destination course.
     * @param int $profileid Enabled profile.
     * @param int $draftid Current-user draft.
     * @param array $items Source selectors.
     * @param array $limits Authoritative owning form options.
     * @param string $digest Previously approved review digest.
     * @param string $basepath Destination directory.
     * @param string $mode Folder placement mode.
     * @param array $options Reviewed mapping roots and destination filenames.
     * @return array Applied review.
     */
    public static function apply(
        int $courseid,
        int $profileid,
        int $draftid,
        array $items,
        array $limits,
        string $digest,
        string $basepath = '/',
        string $mode = 'paths',
        array $options = []
    ): array {

        global $DB, $USER;
        $lock = \core\lock\lock_config::get_lock_factory('local_filebank')
            ->get_lock('draft:' . $USER->id . ':' . $draftid, 10);
        if (!$lock) {
            throw new \moodle_exception('lockfailed', 'local_filebank');
        }
        try {
            $transaction = $DB->start_delegated_transaction();
            $review = self::review($courseid, $profileid, $draftid, $items, $limits, $basepath, $mode, $options);
            if ($review['blocked'] || !hash_equals($review['digest'], $digest)) {
                throw new \moodle_exception('batchconflict', 'local_filebank');
            }
            $context = \context_user::instance($USER->id);
            foreach ($review['operations'] as $operation) {
                if ($operation['decision'] !== 'copy') {
                    continue;
                }
                draft_writer::copy($courseid, $profileid, $draftid, $context, $operation);
            }
            $transaction->allow_commit();
            return $review;
        } catch (\Throwable $exception) {
            if (isset($transaction) && !$transaction->is_disposed()) {
                $transaction->rollback($exception);
            }
            throw $exception;
        } finally {
            $lock->release();
        }
    }
}
