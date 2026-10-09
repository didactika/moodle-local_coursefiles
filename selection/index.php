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
 * CourseBridge native material import integration.
 *
 * @package local_coursebridge
 * @copyright 2026 CourseBridge contributors
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../../config.php');
require_once($CFG->dirroot . '/repository/lib.php');

use local_coursebridge\local\selection\browser_listing;
use local_coursebridge\local\selection\draft_scope;
use local_coursebridge\local\selection\selection_options;
use local_coursebridge\output\display;

$token = required_param('scope', PARAM_ALPHANUM);
require_login();
$scope = draft_scope::describe($token, sesskey());
$course = get_course($scope['courseid']);
$context = context::instance_by_id($scope['contextid']);
$PAGE->set_course($course);
$PAGE->set_context($context);
$PAGE->set_url('/local/coursebridge/selection/index.php', ['scope' => $token]);
$PAGE->set_pagelayout('embedded');
$PAGE->set_title(get_string('importmaterials', 'local_coursebridge'));
$PAGE->set_heading($course->fullname);
$profiles = $DB->get_records_menu('local_coursebridge', ['enabled' => 1], 'name', 'id, name');
$state = $scope['view'] ?? ['profileid' => (int)array_key_first($profiles), 'selected' => [],
    'filters' => ['q' => '', 'edition' => '', 'shortname' => '', 'module' => '', 'mimetype' => ''],
    'folder' => '', 'after' => ''];
$message = '';
$review = null;
$file = null;
try {
    if (data_submitted()) {
        require_sesskey();
        $state['selected'] = array_values(array_unique(optional_param_array('items', [], PARAM_RAW)));
        selection_options::parse($state['selected']);
        $state['profileid'] = required_param('profileid', PARAM_INT);
        foreach (['q', 'edition', 'shortname', 'module', 'mimetype'] as $filter) {
            $state['filters'][$filter] = optional_param($filter, '', PARAM_TEXT);
        }
        if (optional_param('clear', false, PARAM_BOOL)) {
            $state['selected'] = [];
        }
        if (optional_param('selectpage', false, PARAM_BOOL)) {
            $current = browser_listing::page($scope, $state);
            $state['selected'] = array_values(array_unique(array_merge(
                $state['selected'],
                array_column($current['cards'], 'value')
            )));
            selection_options::parse($state['selected']);
        }
        if (optional_param('search', false, PARAM_BOOL)) {
            $state['after'] = '';
            $state['folder'] = '';
        } else if (isset($_POST['openfolder'])) {
            $state['folder'] = optional_param('openfolder', '', PARAM_RAW);
            $state['after'] = '';
        } else if (isset($_POST['next'])) {
            $state['after'] = optional_param('next', '', PARAM_ALPHANUM);
        }
        draft_scope::select_destination($token, optional_param('destination', '/', PARAM_RAW), sesskey());
        draft_scope::select_mode($token, optional_param('mode', 'paths', PARAM_ALPHA), sesskey());
        draft_scope::remember_view($token, $state, sesskey());
        if (optional_param('review', false, PARAM_BOOL)) {
            $review = draft_scope::review($token, $state['profileid'], selection_options::parse($state['selected']), sesskey());
            if (!$review['blocked']) {
                $operation = $review['operations'][0];
                $source = get_file_storage()->get_file_by_hash($operation['pathnamehash']);
                $file = ['title' => $source->get_filename(), 'author' => $source->get_author(),
                    'license' => $source->get_license(), 'thumbnail' => $OUTPUT->image_url('f/document')->out(false),
                    'source' => base64_encode(json_encode(['batch' => $token, 'digest' => $review['digest']]))];
                $file['sourcekey'] = sha1($file['source'] . repository::get_secret_key() . sesskey());
                $PAGE->requires->js_call_amd('local_coursebridge/browser', 'complete', [$file]);
            }
        }
    }
    $scope = draft_scope::describe($token, sesskey());
    $listing = browser_listing::page($scope, $state);
} catch (moodle_exception $exception) {
    $message = $OUTPUT->notification(display::reason($exception->errorcode), 'error');
    $listing = ['cards' => [], 'retained' => array_map(static fn($value) => ['value' => $value], $state['selected']),
        'next' => '', 'parent' => '', 'infolder' => false, 'count' => count($state['selected'])];
}
$rows = [];
foreach ($review['operations'] ?? [] as $operation) {
    $rows[] = ['filename' => $operation['filename'], 'filepath' => $operation['filepath'],
        'author' => $operation['snapshot']['author'], 'license' => $operation['snapshot']['license'],
        'decision' => display::outcome($operation['decision'])];
}
$reviewhtml = $review ? $OUTPUT->render_from_template('local_coursebridge/local/selection/review', [
    'count' => $review['filecount'], 'size' => display_size($review['bytes']), 'rows' => $rows,
]) : '';
$PAGE->requires->js_call_amd('local_coursebridge/browser', 'selection');
echo $OUTPUT->header();
echo $message;
echo $OUTPUT->render_from_template('local_coursebridge/local/selection/browser', $listing + [
    'action' => $PAGE->url->out(false), 'sesskey' => sesskey(), 'scope' => $token,
    'multipleprofiles' => count($profiles) > 1, 'profileid' => $state['profileid'],
    'profiles' => display::options($profiles, (string)$state['profileid']),
    'filters' => $state['filters'], 'modules' => display::options([
        '' => get_string('all'), 'folder' => get_string('modulename', 'folder'), 'resource' => get_string('modulename', 'resource'),
    ], $state['filters']['module']),
    'types' => display::options(display::filetypes(), $state['filters']['mimetype']),
    'destinations' => display::options(draft_scope::destinations($token, sesskey()), $scope['basepath']),
    'modes' => display::options([
        'paths' => get_string('copypaths', 'local_coursebridge'), 'folder' => get_string('copyfolder', 'local_coursebridge'),
        'contents' => get_string('copycontents', 'local_coursebridge'),
    ], $scope['copymode'] ?? 'paths'),
    'reviewhtml' => $reviewhtml, 'approved' => $file !== null,
    'locked' => \local_coursebridge\local\policy\author_policy::locked($course->id),
]);
echo $OUTPUT->footer();
