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
        if ($decision === 'copy' || $decision === 'overwrite') {
            try {
                author_policy::require_native_copy($file, $cm->course, $targetcourse);
            } catch (\moodle_exception $exception) {
                $decision = 'blocked_policy';
            }
        }
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
        if (($filechoices[$choicekey] ?? '') === 'overwrite' && $decision === 'blocked_content') {
            author_policy::require_native_copy($file, $cm->course, $targetcourse);
            $decision = 'overwrite';
        }
        return ['sourcehash' => $file->get_pathnamehash(), 'targetcmid' => $target->id,
        'decision' => $decision, 'snapshot' => $snapshot,
        'included' => !in_array($choicekey, $excluded, true), 'opkey' => $opkey];
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
