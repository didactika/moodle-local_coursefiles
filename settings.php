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
 * Site policy and administration navigation.
 *
 * @package local_coursebridge
 * @copyright 2026 CourseBridge contributors
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

$canviewlog = has_capability('local/coursebridge:viewlog', context_system::instance());
if ($hassiteconfig || $canviewlog) {
    $ADMIN->add('localplugins', new admin_category('local_coursebridge', get_string('pluginname', 'local_coursebridge')));
}
if ($hassiteconfig) {
    $settings = new admin_settingpage('local_coursebridge_settings', get_string('settings', 'local_coursebridge'));
    $settings->add(new admin_setting_configselect(
        'local_coursebridge/author_policy',
        get_string('author_policy', 'local_coursebridge'),
        get_string('author_policy_desc', 'local_coursebridge'),
        'preserve_locked',
        [
            'preserve_locked' => get_string('preserve_locked', 'local_coursebridge'),
            'editable_with_provenance' => get_string('editable_with_provenance', 'local_coursebridge'),
        ]
    ));
    $settings->add(new admin_setting_configselect(
        'local_coursebridge/author_course_override',
        get_string('author_course_override', 'local_coursebridge'),
        get_string('author_course_override_desc', 'local_coursebridge'),
        'disabled',
        [
            'disabled' => get_string('disabled', 'local_coursebridge'),
            'tighten_only' => get_string('tighten_only', 'local_coursebridge'),
        ]
    ));
    $settings->add(new admin_setting_configselect(
        'local_coursebridge/author_missing_policy',
        get_string('author_missing_policy', 'local_coursebridge'),
        get_string('author_missing_policy_desc', 'local_coursebridge'),
        'preserve_unknown',
        [
            'preserve_unknown' => get_string('preserve_unknown', 'local_coursebridge'),
            'block_reuse' => get_string('block_reuse', 'local_coursebridge'),
        ]
    ));
    $ADMIN->add('local_coursebridge', $settings);
    foreach (['profiles', 'migration'] as $page) {
        $ADMIN->add('local_coursebridge', new admin_externalpage(
            'local_coursebridge_' . $page,
            get_string($page === 'profiles' ? 'profiles' : 'preview', 'local_coursebridge'),
            new moodle_url('/local/coursebridge/' . $page . '.php'),
            'local/coursebridge:' . ($page === 'profiles' ? 'manageprofiles' : 'planmigration')
        ));
    }
}
if ($canviewlog) {
    $ADMIN->add('local_coursebridge', new admin_externalpage(
        'local_coursebridge_log',
        get_string('log', 'local_coursebridge'),
        new moodle_url('/local/coursebridge/log.php'),
        'local/coursebridge:viewlog'
    ));
}
