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

/**
 * Load a bounded folder branch without changing the browser's owning folder or cursor.
 *
 * @param array $args Core-validated context, owning scope and folder/cursor selectors.
 * @return string Themeable child nodes and next cursor.
 */
function local_multipicker_output_fragment_browser_tree(array $args): string {
    global $OUTPUT;
    $token = clean_param($args['scope'] ?? '', PARAM_ALPHANUM);
    $scope = \local_multipicker\local\selection\draft_scope::describe($token, sesskey());
    $folder = clean_param($args['folder'] ?? '', PARAM_RAW);
    $allowed = false;
    if (strlen($folder) <= 2048 && str_starts_with($folder, 'folder:')) {
        $item = \local_multipicker\local\selection\selection_options::parse([$folder])[0];
        foreach ($scope['view']['treeroots'] ?? [] as $root) {
            $ancestor = \local_multipicker\local\selection\selection_options::parse([$root])[0];
            if ($item['cmid'] === $ancestor['cmid'] && str_starts_with($item['filepath'], $ancestor['filepath'])) {
                $allowed = true;
                break;
            }
        }
    }
    if ($args['context']->id !== $scope['contextid'] || !$allowed) {
        throw new moodle_exception('formscopeunavailable', 'local_multipicker');
    }
    $state = $scope['view'];
    $state['folder'] = $folder;
    $state['after'] = clean_param($args['after'] ?? '', PARAM_ALPHANUM);
    $listing = \local_multipicker\local\selection\browser_listing::page($scope, $state);
    $view = $scope['view'];
    $view['treefolders'] = array_slice(array_values(array_unique(array_merge(
        $view['treefolders'],
        array_column(array_filter($listing['cards'], static fn($card) => $card['isfolder']), 'value')
    ))), -\local_multipicker\local\selection\material_selection::MAX_FILES);
    \local_multipicker\local\selection\draft_scope::remember_view($token, $view, sesskey());
    return $OUTPUT->render_from_template(
        'local_multipicker/local/selection/tree_children',
        ['cards' => $listing['cards'], 'folder' => $folder, 'next' => $listing['next']]
    );
}

/**
 * Save a bounded review change and render its current canonical state.
 * @param array $args Core-validated system context and serialized request.
 * @return string Themeable review fragment.
 */
function local_multipicker_output_fragment_migration(array $args): string {
    global $OUTPUT;
    require_capability('local/multipicker:planmigration', context_system::instance());
    if (
        $args['context']->id !== context_system::instance()->id || !is_string($args['state'] ?? null)
        || strlen($args['state']) > 131072
    ) {
        throw new invalid_parameter_exception('Invalid migration request');
    }
    try {
        $state = json_decode($args['state'], true, 8, JSON_THROW_ON_ERROR);
    } catch (JsonException $exception) {
        throw new invalid_parameter_exception('Invalid migration document');
    }
    if (!is_array($state) || !confirm_sesskey($state['sesskey'] ?? '')) {
        throw new moodle_exception('invalidsesskey');
    }
    $planid = clean_param($state['planid'] ?? 0, PARAM_INT);
    $action = $state['action'] ?? 'view';
    if ($action === 'resolve') {
        $planid = \local_multipicker\local\migration\plan_resolution::apply(
            $planid,
            clean_param($state['digest'] ?? '', PARAM_ALPHANUM),
            clean_param($state['kind'] ?? '', PARAM_ALPHA),
            clean_param($state['id'] ?? -1, PARAM_INT),
            clean_param($state['choice'] ?? '', PARAM_RAW_TRIMMED)
        );
    } else if (in_array($action, ['page', 'group', 'all', 'none'], true)) {
        \local_multipicker\local\migration\plan_selection::save(
            $planid,
            clean_param($state['digest'] ?? '', PARAM_ALPHANUM),
            $state['visible'] ?? [],
            $state['included'] ?? [],
            $action,
            ['courseid' => (int)($state['courseid'] ?? 0), 'cmid' => (int)($state['cmid'] ?? 0),
            'included' => (int)($state['groupincluded'] ?? 0)]
        );
        $result = \local_multipicker\local\migration\plan_selection::state($planid, $state['visible'] ?? []);
        return html_writer::div('', '', ['data-multipicker-selection-state' => json_encode($result)]);
    } else if ($action !== 'view') {
        throw new invalid_parameter_exception('Unknown migration action');
    }
    $data = \local_multipicker\output\migration_report::data(
        $planid,
        clean_param($state['decision'] ?? '', PARAM_ALPHA),
        (int)($state['page'] ?? 0),
        clean_param($state['q'] ?? '', PARAM_TEXT),
        false,
        (int)($state['issuepage'] ?? 0),
        (int)($state['perpage'] ?? 50)
    );
    return $OUTPUT->render_from_template('local_multipicker/local/migration/report', $data);
}

/**
 * Change selected profile states without replacing the list or its selection.
 *
 * @param array $args Core-validated system context and bounded JSON state.
 * @return string Encoded state of the affected rows only.
 */
function local_multipicker_output_fragment_profiles(array $args): string {
    global $DB, $OUTPUT;
    require_capability('local/multipicker:manageprofiles', context_system::instance());
    if (
        $args['context']->id !== context_system::instance()->id || !is_string($args['state'] ?? null)
            || strlen($args['state']) > 16384
    ) {
        throw new invalid_parameter_exception('Invalid profile request');
    }
    try {
        $state = json_decode($args['state'], true, 4, JSON_THROW_ON_ERROR);
    } catch (JsonException $exception) {
        throw new invalid_parameter_exception('Invalid profile document');
    }
    if (!is_array($state) || !confirm_sesskey($state['sesskey'] ?? '')) {
        throw new moodle_exception('invalidsesskey');
    }
    $ids = $state['ids'] ?? [];
    $action = $state['action'] ?? '';
    if (!is_array($ids) || count($ids) > 50 || !in_array($action, ['enable', 'disable'], true)) {
        throw new invalid_parameter_exception('Unsupported profile change');
    }
    $ids = array_map(static fn($id) => clean_param($id, PARAM_INT), $ids);
    \local_multipicker\local\profile\profiles::change_state($ids, $action);
    $rows = [];
    foreach ($DB->get_records_list('local_multipicker', 'id', $ids) as $profile) {
        $next = $profile->enabled ? 'disable' : 'enable';
        $rows[] = ['id' => (int)$profile->id, 'enabled' => (int)$profile->enabled,
            'status' => \local_multipicker\output\display::outcome($profile->enabled ? 'enabled' : 'disabled'),
            'action' => $next, 'label' => get_string($next),
            'icon' => $OUTPUT->pix_icon($profile->enabled ? 't/hide' : 't/show', '')];
    }
    return html_writer::div('', '', ['data-multipicker-profile-state' => json_encode($rows)]);
}
