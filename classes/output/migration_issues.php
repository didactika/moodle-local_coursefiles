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
 * Paged migration review presentation.
 *
 * @package local_filebank
 * @copyright 2026 Didactika.org
 * @author Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_filebank\output;

/**
 * Present a bounded issue page and only currently authorized resolution choices.
 */
final class migration_issues {
    /**
     * Data.
     *
     * @param array $document Document.
     * @param int $issuepage Issuepage.
     * @param int $perpage Perpage.
     * @param bool $editable Editable.
     * @return array Result.
     */
    public static function data(array $document, int $issuepage, int $perpage, bool $editable): array {
        global $OUTPUT;
        $destinations = [];
        $courses = [];
        $items = [];
        foreach (array_slice($document['issues'], $issuepage * $perpage, $perpage, true) as $index => $issue) {
            $references = self::references($issue, $courses);
            $choices = self::choices($issue, $editable, $destinations);
            $items[] = ['id' => $index, 'description' => display::reason($issue['reason']),
                'context' => implode(' / ', $references), 'choices' => $choices, 'resolvable' => (bool)$choices,
                'help' => $OUTPUT->help_icon('resolvemigration', 'local_filebank')];
        }

        return $items;
    }

    /**
     * References.
     *
     * @param array $issue Issue.
     * @param array $courses Courses.
     * @return array Result.
     */
    private static function references(array $issue, array &$courses): array {
        global $DB;
        $references = [];
        $courseids = array_merge(
            $issue['courses']['source'] ?? [],
            $issue['courses']['target'] ?? [],
            !empty($issue['targetcourseid']) ? [$issue['targetcourseid']] : [],
            !empty($issue['sourcecourseid']) ? [$issue['sourcecourseid']] : []
        );
        if (!empty($issue['sourcecmid'])) {
            $cm = get_coursemodule_from_id(null, $issue['sourcecmid']);
            if ($cm) {
                $courseids[] = $cm->course;
                $references[] = $cm->name;
            }
        }
        foreach (array_unique($courseids) as $courseid) {
            $courses[$courseid] = $courses[$courseid] ?? ($DB->get_field('course', 'shortname', ['id' => $courseid])
                ?: get_string('unknowncourse', 'local_filebank'));
            $references[] = $courses[$courseid];
        }
        return $references;
    }

    /**
     * Choices.
     *
     * @param array $issue Issue.
     * @param bool $editable Editable.
     * @param array $destinations Destinations.
     * @return array Result.
     */
    private static function choices(array $issue, bool $editable, array &$destinations): array {
        $choices = [];
        if ($editable && ($issue['blocking'] ?? true)) {
            $choices[] = ['value' => 'exclude', 'label' => get_string(
                !empty($issue['sourcecmid']) ? 'exclude_source' : 'exclude_pair',
                'local_filebank'
            )];
            if (
                !empty($issue['targetcourseid']) && !empty($issue['sourcecmid'])
                && in_array($issue['reason'], ['placementambiguous', 'creationnamerequired'], true)
            ) {
                $courseid = (int)$issue['targetcourseid'];
                require_capability('moodle/course:manageactivities', \context_course::instance($courseid));
                $destinations[$courseid] ??= self::destinations($courseid);
                $choices = array_merge($choices, $destinations[$courseid]);
            }
        }
        return $choices;
    }

    /**
     * Destinations.
     *
     * @param int $courseid Courseid.
     * @return array Result.
     */
    private static function destinations(int $courseid): array {
        $options = [];
        foreach (get_fast_modinfo($courseid)->get_cms() as $destination) {
            if ($destination->modname === 'folder') {
                $options[] = ['value' => 'folder:' . $destination->id,
                    'label' => get_string(
                        'use_destination',
                        'local_filebank',
                        strip_tags(display::name($destination->name, $destination->context))
                    )];
            }
        }
        if (has_capability('mod/folder:addinstance', \context_course::instance($courseid))) {
            foreach (get_fast_modinfo($courseid)->get_section_info_all() as $section) {
                $label = get_section_name(get_course($courseid), $section);
                $options[] = ['value' => 'create:' . $section->id,
                    'label' => get_string('create_destination', 'local_filebank', strip_tags($label))];
            }
        }
        return $options;
    }
}
