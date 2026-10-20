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
 * A page of what waits for a decision in a migration.
 *
 * @package local_filebank
 * @copyright 2026 Didactika.org
 * @author Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_filebank\local\migration\view;

use local_filebank\output\display;

/**
 * Lists the files in conflict or the issues of one nature, a page at a time, so that a migration with hundreds of them can be read
 * and settled without leaving the page.
 */
final class migration_decisions {
    /**
     * Read a page.
     *
     * @param int $planid The migration.
     * @param string $kind file or issue.
     * @param string $nature The decision of the files or the reason of the issues.
     * @param int $page Zero-based page.
     * @param int $perpage Page size.
     * @return array The total, and the items of the page.
     */
    public static function page(int $planid, string $kind, string $nature, int $page, int $perpage): array {
        require_capability('local/filebank:planmigration', \context_system::instance());
        $perpage = max(1, min(100, $perpage));
        $page = max(0, $page);
        return $kind === 'file' ? self::files($planid, $nature, $page, $perpage) : self::issues($planid, $nature, $page, $perpage);
    }

    /**
     * Files that are in conflict.
     *
     * @param int $planid The migration.
     * @param string $nature The decision.
     * @param int $page Zero-based page.
     * @param int $perpage Page size.
     * @return array The total and the files.
     */
    private static function files(int $planid, string $nature, int $page, int $perpage): array {
        global $DB;
        $conditions = ['planid' => $planid, 'decision' => $nature];
        $total = $DB->count_records('local_filebank_operation', $conditions);
        $names = new course_names();
        $items = [];
        $records = $DB->get_records('local_filebank_operation', $conditions, 'id', '*', $page * $perpage, $perpage);
        foreach ($records as $record) {
            $names->load([$record->sourcecourseid, $record->targetcourseid]);
        }
        foreach ($records as $record) {
            $snapshot = json_decode($record->snapshot, true);
            $source = $snapshot['source'];
            $items[] = ['id' => (int) $record->id,
                'label' => ltrim($source['filepath'] . $source['filename'], '/'),
                'context' => $names->name((int) $record->sourcecourseid) . ' → ' . $names->name((int) $record->targetcourseid)
                    . ' / ' . display::plain($snapshot['target']['name'] ?? ''),
                'detail' => trim(($source['author'] ?: get_string('unknownauthor', 'local_filebank'))
                    . ($source['license'] ? ' · ' . $source['license'] : '')),
                'choices' => migration_overview::file_choices($nature)];
        }
        return ['total' => $total, 'items' => $items];
    }

    /**
     * Issues that were found while the migration was worked out.
     *
     * @param int $planid The migration.
     * @param string $nature The reason.
     * @param int $page Zero-based page.
     * @param int $perpage Page size.
     * @return array The total and the issues.
     */
    private static function issues(int $planid, string $nature, int $page, int $perpage): array {
        global $DB;
        $plan = $DB->get_record('local_filebank_plan', ['id' => $planid], 'id, configjson', MUST_EXIST);
        $document = json_decode($plan->configjson, true);
        $matching = array_filter($document['issues'] ?? [], static fn(array $issue): bool => $issue['reason'] === $nature);
        $total = count($matching);
        $names = new course_names();
        $destinations = [];
        $items = [];
        foreach (array_slice($matching, $page * $perpage, $perpage, true) as $index => $issue) {
            $items[] = ['id' => (int) $index, 'label' => display::reason($issue['reason']),
                'context' => implode(' / ', self::references($issue, $names)), 'detail' => '',
                'choices' => self::choices($issue, $destinations)];
        }
        return ['total' => $total, 'items' => $items];
    }

    /**
     * The folder and the courses that an issue is about.
     *
     * @param array $issue The issue.
     * @param course_names $names Course names.
     * @return string[] The references, as plain text.
     */
    private static function references(array $issue, course_names $names): array {
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
                $references[] = display::plain($cm->name);
            }
        }
        $names->load($courseids);
        foreach (array_unique($courseids) as $courseid) {
            $references[] = $names->name((int) $courseid);
        }
        return $references;
    }

    /**
     * What can be chosen for one issue.
     *
     * @param array $issue The issue.
     * @param array $destinations The destinations already read, by course.
     * @return array The choices with their labels.
     */
    private static function choices(array $issue, array &$destinations): array {
        $choices = [['value' => 'exclude', 'label' => get_string('choice_exclude', 'local_filebank')]];
        if (
            !empty($issue['targetcourseid']) && !empty($issue['sourcecmid'])
            && in_array($issue['reason'], ['placementambiguous', 'creationnamerequired'], true)
        ) {
            $courseid = (int) $issue['targetcourseid'];
            if (has_capability('moodle/course:manageactivities', \context_course::instance($courseid))) {
                $destinations[$courseid] = $destinations[$courseid] ?? self::destinations($courseid);
                $choices = array_merge($choices, $destinations[$courseid]);
            }
        }
        return $choices;
    }

    /**
     * The folders and sections of a course that a folder could go to.
     *
     * @param int $courseid The destination course.
     * @return array The choices with their labels.
     */
    private static function destinations(int $courseid): array {
        $options = [];
        foreach (get_fast_modinfo($courseid)->get_cms() as $destination) {
            if ($destination->modname === 'folder') {
                $options[] = ['value' => 'folder:' . $destination->id,
                    'label' => get_string('use_destination', 'local_filebank', display::plain($destination->name))];
            }
        }
        if (has_capability('mod/folder:addinstance', \context_course::instance($courseid))) {
            foreach (get_fast_modinfo($courseid)->get_section_info_all() as $section) {
                $options[] = ['value' => 'create:' . $section->id, 'label' => get_string(
                    'create_destination',
                    'local_filebank',
                    strip_tags(get_section_name(get_course($courseid), $section))
                )];
            }
        }
        return $options;
    }
}
