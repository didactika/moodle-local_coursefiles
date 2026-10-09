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
 * Administration page registration.
 *
 * @package local_multipicker
 * @copyright 2026 MultiPicker contributors
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

$pages = \local_multipicker\output\navigation::pages();
$visible = array_filter($pages, static fn($page) => has_capability($page['capability'], context_system::instance()));
$configuration = array_filter($visible, static fn($name) => $name !== 'log', ARRAY_FILTER_USE_KEY);
if ($configuration) {
    $ADMIN->add(
        $hassiteconfig ? 'localplugins' : 'root',
        new admin_category('local_multipicker', get_string('pluginname', 'local_multipicker'))
    );
    foreach ($configuration as $name => $page) {
        $ADMIN->add('local_multipicker', new admin_externalpage(
            'local_multipicker_' . $name,
            get_string($page['label'], 'local_multipicker'),
            new moodle_url('/local/multipicker/admin/' . $name . '.php'),
            $page['capability'],
            $name !== array_key_first($configuration)
        ));
    }
}

if (isset($visible['log'])) {
    $ADMIN->add('reports', new admin_externalpage(
        'local_multipicker_log',
        get_string('logreports', 'local_multipicker'),
        new moodle_url('/local/multipicker/admin/log.php'),
        'local/multipicker:viewlog'
    ));
}
