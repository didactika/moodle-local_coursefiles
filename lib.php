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
 * Course navigation integration.
 *
 * @package local_multipicker
 * @copyright 2026 MultiPicker contributors
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Add course material and policy entries.
 *
 * @param navigation_node $navigation Course settings.
 * @param stdClass $course Course.
 * @param context_course $context Course context.
 */
function local_multipicker_extend_navigation_course($navigation, $course, $context): void {
    if (has_capability('local/multipicker:browse', $context)) {
        $navigation->add(
            get_string('dashboard', 'local_multipicker'),
            new moodle_url('/local/multipicker/course/index.php', ['courseid' => $course->id]),
            navigation_node::TYPE_CUSTOM
        );
    } else if (has_capability('local/multipicker:managecoursepolicy', $context)) {
        $navigation->add(
            get_string('coursepolicy', 'local_multipicker'),
            new moodle_url('/local/multipicker/course/policy.php', ['courseid' => $course->id]),
            navigation_node::TYPE_CUSTOM
        );
    }
}

/**
 * Refresh the chooser through Moodle's Fragment API using the real native control authority.
 *
 * @param array $args Context supplied by core and bounded serialized form state.
 * @return string Themeable browser fragment.
 */
function local_multipicker_output_fragment_browser(array $args): string {
    global $PAGE, $OUTPUT;
    $token = clean_param($args['scope'] ?? '', PARAM_ALPHANUM);
    $scope = \local_multipicker\local\selection\draft_scope::describe($token, sesskey());
    if (
        $args['context']->id !== $scope['contextid'] || !is_string($args['state'] ?? null)
            || strlen($args['state']) > 524288
    ) {
        throw new moodle_exception('formscopeunavailable', 'local_multipicker');
    }
    try {
        $raw = json_decode($args['state'], true, 8, JSON_THROW_ON_ERROR);
    } catch (JsonException $exception) {
        throw new moodle_exception('invalidselection', 'local_multipicker');
    }
    if (!is_array($raw)) {
        throw new moodle_exception('invalidselection', 'local_multipicker');
    }
    // Fragments refresh filters and canonical checkbox selections. Review and transfer use their separate native flows.
    foreach (['review', 'clear', 'selectpage', 'selectall', 'openfolder', 'previouspage', 'next', 'removeitem'] as $action) {
        unset($raw[$action]);
    }
    $raw['search'] = false;
    // Checkbox changes retain the server-owned folder, cursor and pagination history.
    $raw['refresh'] = empty($raw['toggleitem']);
    $input = \local_multipicker\local\selection\browser_controller::input($raw);
    $result = \local_multipicker\local\selection\browser_controller::data($token, $input);
    return html_writer::div(
        $result['message']
        . $OUTPUT->render_from_template('local_multipicker/local/selection/browser', $result['data']),
        '',
        ['data-multipicker-panel' => '']
    );
}
