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
 * Reviewed batches for existing native form drafts.
 *
 * @package local_coursebridge
 * @copyright 2026 CourseBridge contributors
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_coursebridge\local\selection;

use local_coursebridge\local\catalog\material_source;
use local_coursebridge\local\migration\copy_decision;
use local_coursebridge\local\policy\author_policy;
use local_coursebridge\local\profile\profiles;

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
     * @return array Decisions, resulting file count/size and eligibility.
     */
    public static function review(
        int $courseid,
        int $profileid,
        int $draftid,
        array $items,
        array $limits,
        string $basepath = '/'
    ): array {
        global $USER;
        require_capability('local/coursebridge:reuse', \context_course::instance($courseid));
        require_capability('moodle/course:manageactivities', \context_course::instance($courseid));
        self::validate_limits($limits);
        $basepath = material_selection::path($basepath);
        $fs = get_file_storage();
        $context = \context_user::instance($USER->id);
        if ($draftid <= 0 || !$fs->get_file($context->id, 'user', 'draft', $draftid, $basepath, '.')) {
            throw new \moodle_exception('invaliddraft', 'local_coursebridge');
        }
        $current = $fs->get_area_files($context->id, 'user', 'draft', $draftid, 'id', false);
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
        [, $revision, $rules] = profiles::load($profileid);
        $skipcontent = ($rules->get_config()['migration']['same_content_different_path'] ?? '') === 'skip-and-report';
        $operations = [];
        $blocked = false;
        foreach (material_selection::resolve($courseid, $profileid, $items) as $hash => $file) {
            [$file, $cm] = material_source::resolve($hash, $courseid, $profileid);
            author_policy::require_native_copy($file, (int)$cm->course, $courseid);
            $source = copy_decision::snapshot($file);
            $path = $basepath . ltrim($file->get_filepath(), '/');
            $key = $path . $file->get_filename();
            $decision = copy_decision::classify($source, $paths[$key] ?? null);
            if ($decision === 'copy' && $skipcontent) {
                foreach ($content[$file->get_contenthash()] ?? [] as $existing) {
                    $decision = copy_decision::classify($source, $existing) === 'skip_identical'
                        ? 'skip_content' : 'blocked_attribution';
                    if ($decision === 'blocked_attribution') {
                        break;
                    }
                }
            }
            if (!$limits['subdirs'] && $path !== '/') {
                throw new \moodle_exception('batchlimit', 'local_coursebridge');
            }
            if (
                !in_array('*', $limits['accepted_types'], true)
                    && !file_extension_in_typegroup($file->get_filename(), $limits['accepted_types'], true)
            ) {
                throw new \moodle_exception('batchlimit', 'local_coursebridge');
            }
            if ($limits['maxbytes'] > 0 && $file->get_filesize() > $limits['maxbytes']) {
                throw new \moodle_exception('batchlimit', 'local_coursebridge');
            }
            if ($decision === 'copy') {
                $count++;
                $bytes += $file->get_filesize();
                $paths[$key] = $source;
                $content[$file->get_contenthash()][] = $source;
            }
            $blocked = $blocked || str_starts_with($decision, 'blocked_');
            $operations[] = ['pathnamehash' => $hash, 'filepath' => $path, 'filename' => $file->get_filename(),
                'decision' => $decision, 'snapshot' => $source];
        }
        if (
            ($limits['maxfiles'] >= 0 && $count > $limits['maxfiles'])
                || ($limits['areamaxbytes'] >= 0 && $bytes > $limits['areamaxbytes'])
        ) {
            throw new \moodle_exception('batchlimit', 'local_coursebridge');
        }
        $digest = hash('sha256', json_encode([
            $USER->id, $courseid, $profileid, $revision->id, $draftid, $basepath, $limits, $draftsnapshot, $operations,
        ], JSON_THROW_ON_ERROR));
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
     * @return array Applied review.
     */
    public static function apply(
        int $courseid,
        int $profileid,
        int $draftid,
        array $items,
        array $limits,
        string $digest,
        string $basepath = '/'
    ): array {
        global $DB, $USER;
        $lock = \core\lock\lock_config::get_lock_factory('local_coursebridge')
            ->get_lock('draft:' . $USER->id . ':' . $draftid, 10);
        if (!$lock) {
            throw new \moodle_exception('lockfailed', 'local_coursebridge');
        }
        try {
            $transaction = $DB->start_delegated_transaction();
            $review = self::review($courseid, $profileid, $draftid, $items, $limits, $basepath);
            if ($review['blocked'] || !hash_equals($review['digest'], $digest)) {
                throw new \moodle_exception('batchconflict', 'local_coursebridge');
            }
            $context = \context_user::instance($USER->id);
            foreach ($review['operations'] as $operation) {
                if ($operation['decision'] !== 'copy') {
                    continue;
                }
                [$file, $cm] = material_source::resolve($operation['pathnamehash'], $courseid, $profileid);
                author_policy::require_native_copy($file, (int)$cm->course, $courseid);
                if (copy_decision::snapshot($file) !== $operation['snapshot']) {
                    throw new \moodle_exception('batchconflict', 'local_coursebridge');
                }
                $attributionid = author_policy::capture($file);
                $uuid = $DB->get_field('local_coursebridge_attribution', 'uuid', ['id' => $attributionid], MUST_EXIST);
                get_file_storage()->create_file_from_storedfile([
                    'contextid' => $context->id, 'component' => 'user', 'filearea' => 'draft', 'itemid' => $draftid,
                    'filepath' => $operation['filepath'], 'filename' => $file->get_filename(), 'userid' => $USER->id,
                    'author' => $file->get_author(), 'license' => $file->get_license(),
                    // A source activity's main-file choice must not replace the destination's choice.
                    'sortorder' => 0,
                    'source' => serialize((object)['source' => json_encode(['coursebridge' => $uuid])]),
                ], $file);
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

    /**
     * Reject incomplete control contracts rather than falling back to unlimited uploads.
     *
     * @param array $limits Server-side options.
     */
    private static function validate_limits(array $limits): void {
        foreach (['maxfiles', 'maxbytes', 'areamaxbytes'] as $name) {
            if (!isset($limits[$name]) || !is_int($limits[$name]) || $limits[$name] < -1) {
                throw new \moodle_exception('batchlimit', 'local_coursebridge');
            }
        }
        if (
            !isset($limits['subdirs'], $limits['accepted_types'])
                || !in_array($limits['subdirs'], [true, false, 0, 1], true)
                || !is_array($limits['accepted_types']) || !$limits['accepted_types']
        ) {
            throw new \moodle_exception('batchlimit', 'local_coursebridge');
        }
        foreach ($limits['accepted_types'] as $type) {
            if (!is_string($type) || $type === '') {
                throw new \moodle_exception('batchlimit', 'local_coursebridge');
            }
        }
    }
}
