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
 * Reviewed immutable family migration planning.
 *
 * @package local_filebank
 * @copyright 2026 Didactika.org
 * @author Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_filebank\local\migration;

use local_filebank\local\catalog\material_source;
use local_filebank\local\profile\profiles;
use local_filebank\local\policy\author_policy;
use local_filebank\local\logging\log;

/**
 * Classify one authorized source leaf against its reviewed destination and author policy.
 */
final class planning_file {
    /**
     * Operation.
     *
     * @param \stored_file $candidate Authorized source candidate.
     * @param object $target Target.
     * @param array $targetcontent Targetcontent.
     * @param array $sourcescope Sourcescope.
     * @param array $context Course and placement snapshots.
     * @return array Result.
     */
    public static function operation(
        \stored_file $candidate,
        object $target,
        array $targetcontent,
        array &$sourcescope,
        array $context
    ): array {
        $sourcecourse = $context['sourcecourse'];
        $targetcourse = $context['targetcourse'];
        $sourcecourseinfo = $context['sourcecourseinfo'];
        $targetcourseinfo = $context['targetcourseinfo'];
        $sourceidentity = $context['sourceidentity'];
        $targetidentity = $context['targetidentity'];
        $targetdata = $context['targetdata'];
        $targetkey = $context['targetkey'];
        $config = $context['config'];
        $filechoices = $context['filechoices'];
        $excluded = $context['excluded'];
        $profileid = $context['profileid'];
        $loaded = $context['loaded'];
        $sourcecm = $context['sourcecm'];
        [$file, $cm] = material_source::resolve(
            $candidate->get_pathnamehash(),
            $targetcourse,
            $profileid,
            $loaded,
            $sourcescope
        );
        $source = copy_decision::snapshot($file);
        $existing = get_file_storage()->get_file(
            $target->context->id,
            'mod_folder',
            'content',
            0,
            $file->get_filepath(),
            $file->get_filename()
        );
        $existingdata = $existing ? copy_decision::snapshot($existing) : null;
        [$decision, $duplicate] = self::decision($file, $source, $existingdata, $config, $targetcontent);
        $decision = self::policy_checked($decision, $file, (int) $cm->course, $targetcourse);
        $snapshot = ['source' => $source, 'sourcecourse' => $sourcecourse,
        'sourceshortname' => $sourcecourseinfo->shortname,
        'targetshortname' => $targetcourseinfo->shortname,
        'target' => $targetdata, 'targetkey' => $targetkey, 'sourcecmid' => $sourcecm->id,
        'sourcefoldername' => $sourcecm->name, 'existing' => $existingdata,
        'duplicate' => $duplicate,
        'sourceidentity' => $sourceidentity,
        'targetidentity' => $targetidentity];
        $opkey = hash('sha256', $file->get_pathnamehash() . ':' . ($targetkey ?: $target->id));
        $choicekey = $file->get_pathnamehash() . ':' . $targetcourse;
        $decision = self::chosen($decision, $filechoices[$choicekey] ?? '', $file, (int) $cm->course, $targetcourse);
        return ['sourcehash' => $file->get_pathnamehash(), 'targetcmid' => $target->id,
        'decision' => $decision, 'snapshot' => $snapshot,
        'included' => !in_array($choicekey, $excluded, true) && !self::unsettled($decision),
        'opkey' => $opkey];
    }

    /**
     * Hold back a file that the author policy does not allow to be copied.
     *
     * @param string $decision What was decided so far.
     * @param \stored_file $file The source file.
     * @param int $sourcecourse The course the file comes from.
     * @param int $targetcourse The course it would go to.
     * @return string blocked_policy when the file would be written and the policy refuses it; the decision otherwise.
     */
    private static function policy_checked(string $decision, \stored_file $file, int $sourcecourse, int $targetcourse): string {
        if ($decision !== 'copy' && $decision !== 'overwrite') {
            return $decision;
        }
        try {
            author_policy::require_native_copy($file, $sourcecourse, $targetcourse);
        } catch (\moodle_exception $exception) {
            return 'blocked_policy';
        }
        return $decision;
    }

    /**
     * Apply what the person chose for a file that is in conflict.
     *
     * @param string $decision What was decided without the person.
     * @param string $choice overwrite, skip or an empty string.
     * @param \stored_file $file The source file.
     * @param int $sourcecourse The course the file comes from.
     * @param int $targetcourse The course it would go to.
     * @return string The decision; skip_conflict for a conflict left as it is and overwrite for one that was overwritten.
     */
    private static function chosen(
        string $decision,
        string $choice,
        \stored_file $file,
        int $sourcecourse,
        int $targetcourse
    ): string {
        if ($choice === 'skip' && str_starts_with($decision, 'blocked_')) {
            // The conflict is settled and the file stays out of the migration.
            return 'skip_conflict';
        }
        if ($choice === 'overwrite' && $decision === 'blocked_content') {
            author_policy::require_native_copy($file, $sourcecourse, $targetcourse);
            return 'overwrite';
        }
        return $decision;
    }

    /**
     * Whether a decision keeps the file out of the migration until a person changes it.
     *
     * @param string $decision The decision.
     * @return bool True for a conflict or a conflict that was left as it is.
     */
    private static function unsettled(string $decision): bool {
        return str_starts_with($decision, 'blocked_') || $decision === 'skip_conflict';
    }

    /**
     * Decision.
     *
     * @param \stored_file $file File.
     * @param array $source Source.
     * @param array|null $existingdata Existing destination snapshot.
     * @param array $config Config.
     * @param array $targetcontent Targetcontent.
     * @return array Result.
     */
    private static function decision(
        \stored_file $file,
        array $source,
        ?array $existingdata,
        array $config,
        array $targetcontent
    ): array {

        $decision = copy_decision::classify($source, $existingdata);
        $duplicate = null;
        if ($decision === 'blocked_content' && ($config['same_path_different_content'] ?? '') === 'overwrite') {
                $decision = 'overwrite';
        } else if ($decision === 'blocked_attribution' && ($config['identical_content'] ?? '') === 'skip') {
            $decision = 'skip_content';
            $duplicate = $existingdata;
        }
        return self::duplicates($file, $source, $config, $targetcontent, $decision, $duplicate);
    }

    /**
     * Duplicates.
     *
     * @param \stored_file $file File.
     * @param array $source Source.
     * @param array $config Config.
     * @param array $targetcontent Targetcontent.
     * @param string $decision Decision.
     * @param array|null $duplicate Existing same-path duplicate.
     * @return array Result.
     */
    private static function duplicates(
        \stored_file $file,
        array $source,
        array $config,
        array $targetcontent,
        string $decision,
        ?array $duplicate
    ): array {
        if (
            $decision === 'copy'
            && ($config['same_content_different_path'] ?? 'skip-and-report') !== 'keep'
        ) {
            foreach ($targetcontent[$file->get_contenthash()] ?? [] as $targetfile) {
                $duplicate = copy_decision::snapshot($targetfile);
                $decision = (($config['identical_content'] ?? '') === 'skip'
                    || copy_decision::classify($source, $duplicate) === 'skip_identical')
                ? 'skip_content' : 'blocked_attribution';
                if ($decision === 'blocked_attribution') {
                    break;
                }
            }
        }

        return [$decision, $duplicate];
    }
}
