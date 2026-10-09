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
 * Course-scoped material catalog and publication controls.
 *
 * @package local_coursebridge
 * @copyright 2026 CourseBridge contributors
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../../config.php');

use local_coursebridge\output\display;
use local_coursebridge\output\view;

$courseid = required_param('courseid', PARAM_INT);
$profileid = optional_param('profileid', 0, PARAM_INT);
$course = get_course($courseid);
require_login($course);
$context = context_course::instance($courseid);
require_capability('local/coursebridge:browse', $context);
$PAGE->set_context($context);
$PAGE->set_pagelayout('incourse');
$PAGE->set_url('/local/coursebridge/course/index.php', ['courseid' => $courseid, 'profileid' => $profileid]);
$PAGE->set_title(get_string('dashboard', 'local_coursebridge'));
$PAGE->set_heading(format_string($course->fullname));
$profiles = $DB->get_records('local_coursebridge', ['enabled' => 1], 'name');
if (!$profileid && $profiles) {
    $profileid = (int)reset($profiles)->id;
}
if (data_submitted() && optional_param('action', '', PARAM_ALPHA) !== '') {
    require_sesskey();
    $action = required_param('action', PARAM_ALPHA);
    if ($action === 'refresh') {
        require_capability('moodle/course:manageactivities', $context);
        $task = new \local_coursebridge\task\refresh_course();
        $task->set_custom_data(['courseid' => $courseid]);
        \core\task\manager::queue_adhoc_task($task, true);
        $message = get_string('refreshqueued', 'local_coursebridge');
    } else if ($action === 'publish' || $action === 'revoke') {
        $cm = get_coursemodule_from_id(null, required_param('cmid', PARAM_INT), $courseid, false, MUST_EXIST);
        \local_coursebridge\local\catalog\material_source::publish($profileid, $cm->id, $action === 'revoke');
        $message = get_string('published', 'local_coursebridge');
    } else {
        throw new invalid_parameter_exception('Unknown catalog action');
    }
    redirect(new moodle_url('/local/coursebridge/course/index.php', [
        'courseid' => $courseid, 'profileid' => $profileid,
    ]), $message);
}
$filters = [];
foreach (['q', 'edition', 'shortname', 'module', 'mimetype'] as $name) {
    $filters[$name] = optional_param($name, '', PARAM_TEXT);
}
$reseturl = new moodle_url('/local/coursebridge/course/index.php', ['courseid' => $courseid, 'profileid' => $profileid]);
$options = [];
foreach ($profiles as $profile) {
    $options[$profile->id] = $profile->name;
}
$data = [
    'actionurl' => $PAGE->url->out(false), 'courseid' => $courseid, 'profileid' => $profileid,
    'sesskey' => sesskey(), 'profiles' => display::options($options, (string)$profileid),
    'hasprofiles' => (bool)$profiles, 'filters' => $filters, 'reseturl' => $reseturl->out(false),
    'multipleprofiles' => count($profiles) > 1,
    'advancedfilters' => $filters['shortname'] !== '' || $filters['module'] !== '' || $filters['mimetype'] !== '',
    'modules' => display::options([
        '' => get_string('allmodules', 'local_coursebridge'),
        'folder' => get_string('modulename', 'folder'),
        'resource' => get_string('modulename', 'resource'),
    ], $filters['module']),
    'filetypes' => display::options(display::filetypes(), $filters['mimetype']),
    'canrefresh' => has_capability('moodle/course:manageactivities', $context),
    'assets' => [], 'publicationenabled' => false,
];
$sourcecourses = [];
if (has_capability('local/coursebridge:managecoursepolicy', $context)) {
    $data['policyurl'] = (new moodle_url('/local/coursebridge/course/policy.php', ['courseid' => $courseid]))->out(false);
}
if ($profileid) {
    $result = \local_coursebridge\local\catalog\catalog::search(
        $courseid,
        $profileid,
        $filters,
        optional_param('after', '', PARAM_ALPHANUM)
    );
    foreach ($result['assets'] as $asset) {
        $asset['filename'] = $asset['occurrences'][0]['filename'];
        $asset['size'] = display_size($asset['filesize']);
        $asset['origincount'] = count($asset['occurrences']);
        foreach ($asset['occurrences'] as &$origin) {
            $sourcecourses[$origin['courseid']] = $sourcecourses[$origin['courseid']] ?? get_course($origin['courseid']);
            if (can_access_course($sourcecourses[$origin['courseid']], null, '', true)) {
                $origin['courseurl'] = (new moodle_url('/course/view.php', ['id' => $origin['courseid']]))->out(false);
            }
            $origin['editionlabel'] = $origin['edition'] ?? get_string('noedition', 'local_coursebridge');
            $origin['authorlabel'] = $origin['author'] !== ''
                ? $origin['author'] : get_string('unknownauthor', 'local_coursebridge');
        }
        unset($origin);
        $data['assets'][] = $asset;
    }
    if ($result['next']) {
        $data['nexturl'] = (new moodle_url('/local/coursebridge/course/index.php', [
            'courseid' => $courseid, 'profileid' => $profileid, 'after' => $result['next'],
        ] + $filters))->out(false);
    }
    [$profile, $revision, $rules] = \local_coursebridge\local\profile\profiles::load($profileid);
    $data['publicationenabled'] = ($rules->get_config()['publication']['mode'] ?? '') === 'explicit-family-publication'
        && has_capability('local/coursebridge:publish', $context) && $data['canrefresh'];
    if ($data['publicationenabled']) {
        $grants = $DB->get_records('local_coursebridge_publication', ['profileid' => $profileid], '', 'cmid, revoked');
        $data['publicationmodules'] = [];
        foreach (get_fast_modinfo($course)->get_cms() as $cm) {
            if (!in_array($cm->modname, ['folder', 'resource'], true)) {
                continue;
            }
            $label = $cm->name;
            if (isset($grants[$cm->id]) && !$grants[$cm->id]->revoked) {
                $label .= ' — ' . get_string('publishedlabel', 'local_coursebridge');
            }
            $data['publicationmodules'][] = ['value' => $cm->id, 'label' => $label];
        }
        $data['haspublicationmodules'] = (bool)$data['publicationmodules'];
    }
}
$data['hasassets'] = (bool)$data['assets'];
echo $OUTPUT->header();
echo $PAGE->get_renderer('local_coursebridge')->render(\local_coursebridge\output\navigation::course('index', $courseid));
echo $PAGE->get_renderer('local_coursebridge')->render(new view('catalog', $data));
echo $OUTPUT->footer();
