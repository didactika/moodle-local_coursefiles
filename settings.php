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
 * Administration page registration.
 *
 * @package local_filebank
 * @copyright 2026 Didactika.org
 * @author Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

$pages = \local_filebank\output\navigation::pages();
$visible = array_filter($pages, static fn($page) => has_capability($page['capability'], context_system::instance()));
$configuration = array_filter($visible, static fn($name) => $name !== 'log', ARRAY_FILTER_USE_KEY);
if ($configuration) {
    $ADMIN->add(
        $hassiteconfig ? 'localplugins' : 'root',
        new admin_category('local_filebank', get_string('pluginname', 'local_filebank'), true)
    );
    foreach ($configuration as $name => $page) {
        $ADMIN->add($name === array_key_first($configuration) ? ($hassiteconfig ? 'localplugins' : 'root')
            : 'local_filebank', new admin_externalpage(
                'local_filebank_' . $name,
                get_string($name === array_key_first($configuration) ? 'pluginname' : $page['label'], 'local_filebank'),
                new moodle_url('/local/filebank/admin/' . $name . '.php'),
                $page['capability'],
                $name !== array_key_first($configuration)
            ));
    }
}

if (isset($visible['log'])) {
    $ADMIN->add('reports', new admin_externalpage(
        'local_filebank_log',
        get_string('logreports', 'local_filebank'),
        new moodle_url('/local/filebank/admin/log.php'),
        'local/filebank:viewlog'
    ));
}
