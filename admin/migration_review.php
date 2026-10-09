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
 * Immutable migration review and explicit execution.
 *
 * @package local_coursebridge
 * @copyright 2026 CourseBridge contributors
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../../config.php');
require_once($CFG->libdir . '/adminlib.php');

use local_coursebridge\output\migration_report;
use local_coursebridge\output\navigation;
use local_coursebridge\output\view;

$planid = required_param('planid', PARAM_INT);
$url = new moodle_url('/local/coursebridge/admin/migration_review.php', ['planid' => $planid]);
admin_externalpage_setup('local_coursebridge_migration', '', ['planid' => $planid], $url);
if (data_submitted()) {
    require_sesskey();
    $action = required_param('action', PARAM_ALPHA);
    if ($action === 'execute') {
        if (!required_param('confirmed', PARAM_BOOL)) {
            throw new moodle_exception('reviewrequired', 'local_coursebridge');
        }
        \local_coursebridge\local\migration\migration_executor::queue($planid, required_param('digest', PARAM_ALPHANUM));
        $message = get_string('migrationqueued', 'local_coursebridge');
    } else if ($action === 'cancel') {
        $runid = required_param('runid', PARAM_INT);
        $DB->get_record('local_coursebridge_run', ['id' => $runid, 'planid' => $planid], '*', MUST_EXIST);
        \local_coursebridge\local\migration\migration_executor::cancel($runid);
        $message = get_string('migrationcancelled', 'local_coursebridge');
    } else {
        throw new invalid_parameter_exception('Unknown migration action');
    }
    redirect($url, $message, null, \core\output\notification::NOTIFY_SUCCESS);
}
$data = migration_report::data($planid, optional_param('decision', '', PARAM_ALPHA), optional_param('page', 0, PARAM_INT));
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('pluginname', 'local_coursebridge'));
$renderer = $PAGE->get_renderer('local_coursebridge');
echo $renderer->render(navigation::site('migration'));
echo $renderer->render(new view('migration_report', $data));
echo $OUTPUT->footer();
