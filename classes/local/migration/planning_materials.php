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
 * Authorize public source modules and prepare leaf decisions for each course pair.
 */
final class planning_materials {
    /**
     * Pair.
     *
     * @param array $pair Pair.
     * @param array $request Request.
     * @param array $operations Operations.
     * @param array $issues Issues.
     * @param array $creations Creations.
     */
    public static function pair(
        array $pair,
        array $request,
        array &$operations,
        array &$issues,
        array &$creations
    ): void {
        [, , $rules] = $request['loaded'];
        $config = $request['config'];
        if (empty($pair['source'])) {
            return;
        }
        if (count($pair['source']) !== 1 || count($pair['target'] ?? []) !== 1) {
            $issues[] = ['reason' => 'ambiguous_course_pair', 'courses' => $pair];
            return;
        }
        $sourcecourse = $pair['source'][0];
        $targetcourse = $pair['target'][0];
        require_capability('moodle/course:manageactivities', \context_course::instance($targetcourse));
        $sourcescope = [];
        $sourcecourseinfo = get_course($sourcecourse);
        $targetcourseinfo = get_course($targetcourse);
        $sourceidentity = $rules->resolve($sourcecourseinfo);
        $targetidentity = $rules->resolve($targetcourseinfo);
        $request += compact(
            'sourcecourse',
            'targetcourse',
            'sourcecourseinfo',
            'targetcourseinfo',
            'sourceidentity',
            'targetidentity'
        );
        foreach ($config['correspondences'] ?? [$config] as $correspondence) {
            self::correspondence($correspondence, $request, $sourcescope, $operations, $issues, $creations);
        }
    }

    /**
     * Correspondence.
     *
     * @param array $correspondence Correspondence.
     * @param array $request Request.
     * @param array $sourcescope Sourcescope.
     * @param array $operations Operations.
     * @param array $issues Issues.
     * @param array $creations Creations.
     */
    private static function correspondence(
        array $correspondence,
        array $request,
        array &$sourcescope,
        array &$operations,
        array &$issues,
        array &$creations
    ): void {
        $matched = false;
        foreach (get_fast_modinfo($request['sourcecourse'])->get_cms() as $sourcecm) {
            $matched = self::source(
                $sourcecm,
                $correspondence,
                $request,
                $sourcescope,
                $operations,
                $issues,
                $creations
            ) || $matched;
        }
        if (!$matched) {
            $issues[] = ['reason' => 'sourcefoldermissing', 'sourcecourseid' => $request['sourcecourse'], 'blocking' => false];
        }
    }

    /**
     * Source.
     *
     * @param \cm_info $sourcecm Sourcecm.
     * @param array $correspondence Correspondence.
     * @param array $request Request.
     * @param array $sourcescope Sourcescope.
     * @param array $operations Operations.
     * @param array $issues Issues.
     * @param array $creations Creations.
     * @return bool Result.
     */
    private static function source(
        \cm_info $sourcecm,
        array $correspondence,
        array $request,
        array &$sourcescope,
        array &$operations,
        array &$issues,
        array &$creations
    ): bool {

        [$profile, $revision, $rules] = $request['loaded'];
        $config = $request['config'];
        $placements = $request['placements'];
        $filechoices = $request['filechoices'];
        $excluded = $request['excluded'];
        $profileid = $request['profileid'];
        $sourcecourse = $request['sourcecourse'];
        $targetcourse = $request['targetcourse'];
        $sourcecourseinfo = $request['sourcecourseinfo'];
        $targetcourseinfo = $request['targetcourseinfo'];
        $sourceidentity = $request['sourceidentity'];
        $targetidentity = $request['targetidentity'];
        $captures = placement::match($sourcecm, $correspondence['source_module'] ?? []);
        if ($captures === null) {
            return false;
        }
        if (!self::visible($sourcecm, $sourcecourseinfo)) {
            return true;
        }
        $placementkey = $sourcecm->id . ':' . $targetcourse;
        $override = $placements[$placementkey] ?? [];
        if (!empty($override['exclude'])) {
            return true;
        }
        $captures += ['name' => $sourcecm->name, 'idnumber' => $sourcecm->idnumber];
        try {
            // Authorize public leaves before producing destination errors or creation requests.
            $files = self::files(
                $sourcecm,
                $rules->get_config()['publication']['allowed_sources']
                    ?? ['mod_folder/content', 'mod_resource/content']
            );
            if (!$files) {
                return true;
            }
            foreach ($files as $candidate) {
                material_source::resolve(
                    $candidate->get_pathnamehash(),
                    $targetcourse,
                    $profileid,
                    [$profile, $revision, $rules],
                    $sourcescope
                );
            }
            [$target, $targetdata, $targetkey] = planning_destination::resolve(
                $sourcecm,
                $targetcourse,
                $config,
                $correspondence,
                $captures,
                $override,
                $creations
            );
            $targetcontent = self::target_content($target);
            foreach ($files as $candidate) {
                $context = compact(
                    'sourcecourse',
                    'targetcourse',
                    'sourcecourseinfo',
                    'targetcourseinfo',
                    'sourceidentity',
                    'targetidentity',
                    'targetdata',
                    'targetkey',
                    'config',
                    'filechoices',
                    'excluded',
                    'profileid',
                    'sourcecm'
                );
                $context['loaded'] = [$profile, $revision, $rules];
                $operation = planning_file::operation($candidate, $target, $targetcontent, $sourcescope, $context);
                $operations[$operation['opkey']] = $operation;
            }
        } catch (\moodle_exception $exception) {
            $issues[] = ['sourcecmid' => $sourcecm->id, 'sourcecourseid' => $sourcecourse,
            'targetcourseid' => $targetcourse,
            'reason' => $exception->errorcode];
        }
        return true;
    }

    /**
     * Files.
     *
     * @param \cm_info $sourcecm Current native source module.
     * @param array $allowedareas Profile-declared public teaching areas.
     * @return array Result.
     */
    private static function files(\cm_info $sourcecm, array $allowedareas): array {
        $provider = \local_filebank\local\catalog\source_registry::provider($sourcecm->modname);
        $files = [];
        foreach ($provider ? $provider->files($sourcecm) : [] as $candidate) {
            if (
                \local_filebank\local\catalog\source_registry::enabled(
                    $allowedareas
                    ?? ['mod_folder/content', 'mod_resource/content'],
                    $candidate
                )
            ) {
                $files[] = $candidate;
            }
        }
        return $files;
    }

    /**
     * Visible.
     *
     * @param \cm_info $sourcecm Sourcecm.
     * @param \stdClass $sourcecourseinfo Sourcecourseinfo.
     * @return bool Result.
     */
    private static function visible(\cm_info $sourcecm, \stdClass $sourcecourseinfo): bool {
        $sourcesection = get_fast_modinfo($sourcecm->course)->get_section_info($sourcecm->sectionnum);
        if (
            !$sourcecourseinfo->visible || !$sourcecm->visible || !empty($sourcecm->availability)
            || !$sourcesection->visible || !empty($sourcesection->availability)
        ) {
            return false;
        }
        return true;
    }

    /**
     * Index current destination content for duplicate decisions.
     *
     * @param object $target Target.
     * @return array Result.
     */
    private static function target_content(object $target): array {
        $targetcontent = [];
        foreach (
            get_file_storage()->get_area_files(
                $target->context->id,
                'mod_folder',
                'content',
                0,
                'id',
                false
            ) as $targetfile
        ) {
            $targetcontent[$targetfile->get_contenthash()][] = $targetfile;
        }
        return $targetcontent;
    }
}
