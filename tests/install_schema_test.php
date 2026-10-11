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
 * Initial XMLDB schema validation.
 *
 * @package local_filebank
 * @copyright 2026 Didactika.org
 * @author Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_filebank;

/**
 * Check the first-release schema using Moodle's native XMLDB parser.
 * @coversNothing
 */
final class install_schema_test extends \advanced_testcase {
    /**
     * Required character fields must not declare an empty default.
     */
    public function test_install_schema_loads_without_invalid_char_defaults(): void {
        $this->resetAfterTest();
        $file = new \xmldb_file(__DIR__ . '/../db/install.xml');
        $this->assertTrue($file->loadXMLStructure());
        $structure = $file->getStructure();
        $field = $structure->getTable('local_filebank_author')->getField('parentuuid');
        $this->assertNull($field->getDefault());
        $this->assertTrue($field->getNotNull());
    }
}
