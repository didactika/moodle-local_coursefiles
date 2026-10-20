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
 * The size of a page of a list is one of the values offered for it.
 *
 * @package local_filebank
 * @copyright 2026 Didactika.org
 * @author Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_filebank;

use local_filebank\output\pagination;

/**
 * The sizes a list offers and the rows a page holds, whatever an address asks for.
 *
 * @covers \local_filebank\output\pagination
 */
final class output_pagination_test extends \advanced_testcase {
    /**
     * A list offers the sizes smaller than it and "All", and a short list only "All".
     */
    public function test_the_sizes_offered_follow_the_length_of_the_list(): void {
        $this->assertSame([pagination::ALL], pagination::choices(8));
        $this->assertSame([12, pagination::ALL], pagination::choices(20));
        $this->assertSame([12, 24, pagination::ALL], pagination::choices(46));
        $this->assertSame([12, 24, 48, 96, pagination::ALL], pagination::choices(300));
        $this->assertSame([12, 24, 48, 96], pagination::choices(pagination::MAX_ALL + 1), 'A long list never loads at once');
    }

    /**
     * A size that is not offered is never used: it becomes the whole list or the first size, and the select shows what is shown.
     */
    public function test_a_size_that_is_not_offered_is_never_used(): void {
        $this->assertSame(46, pagination::rows(96, 46), 'More rows than the list hold the whole list');
        $this->assertSame(pagination::ALL, pagination::value(46, 46));
        $this->assertSame(20, pagination::rows(24, 20), 'A size not offered for a list it covers holds the whole list');
        $this->assertSame(12, pagination::rows(12, 20));
        $this->assertSame(20, pagination::rows(pagination::ALL, 20));
        $this->assertSame(12, pagination::rows(pagination::ALL, pagination::MAX_ALL + 1), 'No "All" for a long list');
        $this->assertSame(24, pagination::rows(24, 300));
        $this->assertSame(24, pagination::value(24, 300));
        $this->assertSame(12, pagination::rows(12, 300));
    }

    /**
     * The address cannot ask for a size of its own.
     */
    public function test_the_address_cannot_choose_any_size(): void {
        foreach ([25 => 12, 100 => 12, 10000 => 12, -3 => 12, 48 => 48, 0 => pagination::ALL] as $asked => $expected) {
            $_GET['perpage'] = (string) $asked;
            $this->assertSame($expected, pagination::size(), "perpage={$asked}");
        }
        unset($_GET['perpage']);
    }
}
