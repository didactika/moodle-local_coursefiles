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
 * MultiPicker site settings entry point.
 *
 * @package local_multipicker
 * @copyright 2026 Didactika.org
 * @author Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../../config.php');
require_once($CFG->libdir . '/adminlib.php');

use local_multipicker\form\settings_form;
use local_multipicker\output\navigation;

admin_externalpage_setup('local_multipicker_settings');
$form = new settings_form();
if ($data = $form->get_data()) {
    $changes = [];
    foreach (settings_form::choices() as $name => $values) {
        $previous = get_config('local_multipicker', $name);
        if ($previous !== $data->$name) {
            set_config($name, $data->$name, 'local_multipicker');
            add_to_config_log($name, $previous, $data->$name, 'local_multipicker');
            $changes[$name] = ['previous' => $previous, 'current' => $data->$name];
        }
    }
    foreach (['logretentiondays', 'migrationretentiondays', 'versionretentiondays', 'versionretentionmax'] as $name) {
        $previous = get_config('local_multipicker', $name);
        if ((string)$previous !== (string)$data->$name) {
            set_config($name, $data->$name, 'local_multipicker');
            add_to_config_log($name, $previous, $data->$name, 'local_multipicker');
            $changes[$name] = ['previous' => $previous, 'current' => $data->$name];
        }
    }
    if ($changes) {
        \local_multipicker\local\logging\log::record('site_policy_changed', \core\uuid::generate(), $changes);
    }
    redirect($PAGE->url, get_string('changessaved', 'core'), null, \core\output\notification::NOTIFY_SUCCESS);
}
$defaults = [];
foreach (settings_form::choices() as $name => $values) {
    $defaults[$name] = get_config('local_multipicker', $name) ?: $values[0];
}
foreach (
    [
    'logretentiondays' => 90, 'migrationretentiondays' => 365, 'versionretentiondays' => 0, 'versionretentionmax' => 0,
    ] as $name => $days
) {
    $saved = get_config('local_multipicker', $name);
    $defaults[$name] = $saved === false ? $days : (int)$saved;
}
$form->set_data($defaults);
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('pluginname', 'local_multipicker'));
echo $PAGE->get_renderer('local_multipicker')->render(navigation::site('settings'));
echo $OUTPUT->heading(get_string('settings', 'local_multipicker'), 3);
echo html_writer::tag('p', get_string('settingsintro', 'local_multipicker'), ['class' => 'text-muted']);
$form->display();
echo $OUTPUT->footer();
