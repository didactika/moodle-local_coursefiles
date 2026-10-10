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
 * @package local_multipicker
 * @copyright 2026 MultiPicker contributors
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../../config.php');
require_once($CFG->libdir . '/adminlib.php');

use local_multipicker\output\migration_report;
use local_multipicker\output\navigation;
use local_multipicker\output\view;

$planid = required_param('planid', PARAM_INT);
$url = new moodle_url('/local/multipicker/admin/migration_review.php', ['planid' => $planid]);
admin_externalpage_setup('local_multipicker_migration', '', ['planid' => $planid], $url);
if (data_submitted()) {
    require_sesskey();
    $action = required_param('action', PARAM_ALPHA);
    if ($action === 'group') {
        \local_multipicker\local\migration\plan_selection::save(
            $planid,
            required_param('digest', PARAM_ALPHANUM),
            optional_param_array('visible', [], PARAM_INT),
            optional_param_array('included', [], PARAM_INT),
            'group',
            ['courseid' => required_param('groupcourse', PARAM_INT), 'cmid' => optional_param('groupcmid', 0, PARAM_INT),
                'included' => required_param('groupincluded', PARAM_INT)]
        );
        $message = get_string('selectionupdated', 'local_multipicker');
    } else if ($action === 'execute') {
        if (!required_param('confirmed', PARAM_BOOL)) {
            throw new moodle_exception('reviewrequired', 'local_multipicker');
        }
        \local_multipicker\local\migration\migration_executor::queue($planid, required_param('digest', PARAM_ALPHANUM));
        $message = get_string('migrationqueued', 'local_multipicker');
    } else if (in_array($action, ['selection', 'all', 'none'], true)) {
        \local_multipicker\local\migration\plan_selection::save(
            $planid,
            required_param('digest', PARAM_ALPHANUM),
            optional_param_array('visible', [], PARAM_INT),
            optional_param_array('included', [], PARAM_INT),
            $action === 'selection' ? 'page' : $action
        );
        $message = get_string('selectionupdated', 'local_multipicker');
        $url->params(['q' => optional_param('q', '', PARAM_TEXT), 'page' => optional_param('page', 0, PARAM_INT),
            'decision' => optional_param('decision', '', PARAM_ALPHA),
            'issuepage' => optional_param('issuepage', 0, PARAM_INT)]);
        if (optional_param('navigate', false, PARAM_BOOL)) {
            $url->params(['q' => optional_param('nextq', '', PARAM_TEXT), 'page' => optional_param('nextpage', 0, PARAM_INT),
                'decision' => optional_param('nextdecision', '', PARAM_ALPHA),
                'issuepage' => optional_param('nextissuepage', 0, PARAM_INT),
                'step' => optional_param('nextstep', '', PARAM_ALPHA) === 'execute' ? 'execute' : '']);
        }
    } else if ($action === 'cancel') {
        $runid = required_param('runid', PARAM_INT);
        $DB->get_record('local_multipicker_run', ['id' => $runid, 'planid' => $planid], '*', MUST_EXIST);
        \local_multipicker\local\migration\migration_executor::cancel($runid);
        $message = get_string('migrationcancelled', 'local_multipicker');
    } else {
        throw new invalid_parameter_exception('Unknown migration action');
    }
    redirect($url, $message, null, \core\output\notification::NOTIFY_SUCCESS);
}
$data = migration_report::data(
    $planid,
    optional_param('decision', '', PARAM_ALPHA),
    optional_param('page', 0, PARAM_INT),
    optional_param('q', '', PARAM_TEXT),
    optional_param('step', '', PARAM_ALPHA) === 'execute',
    optional_param('issuepage', 0, PARAM_INT)
);
$PAGE->requires->js_call_amd('local_multipicker/administration', 'init');
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('pluginname', 'local_multipicker'));
$renderer = $PAGE->get_renderer('local_multipicker');
echo $renderer->render(navigation::site('migration'));
echo $renderer->render(new view('migration_report', $data));
echo $OUTPUT->footer();
