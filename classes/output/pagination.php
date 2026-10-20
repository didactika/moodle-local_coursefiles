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

namespace local_filebank\output;

/**
 * One bounded pagination contract for administration lists and review pages.
 * @package local_filebank
 * @copyright 2026 Didactika.org
 * @author Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class pagination {
    /** @var int[] The sizes a page can have, as block_course_finder offers them. */
    public const SIZES = [12, 24, 48, 96];

    /** @var int The value that asks for the whole list ("All"). */
    public const ALL = 0;

    /** @var int The most rows that "All" shows, so a long list never loads at once. */
    public const MAX_ALL = 500;

    /**
     * Read the size of a page that the address asks for: one of the sizes or "All"; anything else is the first size.
     * @return int The size asked for, or ALL.
     */
    public static function size(): int {
        $size = optional_param('perpage', self::SIZES[0], PARAM_INT);
        return in_array($size, self::SIZES, true) || $size === self::ALL ? $size : self::SIZES[0];
    }

    /**
     * The values to offer for a list: the sizes smaller than the list and "All" when the list is not too long.
     * @param int $total Result count.
     * @return int[] The values, ALL standing for the whole list.
     */
    public static function choices(int $total): array {
        if ($total <= self::SIZES[0]) {
            return [self::ALL];
        }
        $sizes = array_values(array_filter(self::SIZES, static fn(int $size): bool => $size < $total));
        return $total <= self::MAX_ALL ? array_merge($sizes, [self::ALL]) : $sizes;
    }

    /**
     * How many rows a page holds for a size asked for: always one of the values offered for the list.
     * @param int $size The size asked for, or ALL.
     * @param int $total Result count.
     * @return int Rows of a page.
     */
    public static function rows(int $size, int $total): int {
        $choices = self::choices($total);
        $all = in_array(self::ALL, $choices, true);
        if ($size !== self::ALL && in_array($size, $choices, true)) {
            return $size;
        }
        if ($all && ($size === self::ALL || $size >= $total)) {
            return max(1, $total);
        }
        return self::SIZES[0];
    }

    /**
     * The value of the select for the rows a page holds.
     * @param int $rows Rows of a page.
     * @param int $total Result count.
     * @return int One of the sizes, or ALL.
     */
    public static function value(int $rows, int $total): int {
        return in_array($rows, self::SIZES, true) && $rows < $total ? $rows : self::ALL;
    }

    /**
     * Clamp a page against the filtered result count.
     * @param int $total Result count.
     * @param int $page Requested zero-based page.
     * @param int $size Rows of a page.
     * @return int Valid page.
     */
    public static function clamp(int $total, int $page, int $size): int {
        return max(0, min($page, max(0, (int)ceil($total / max(1, $size)) - 1)));
    }

    /**
     * Render the range, the arrows and the size of a page.
     * @param int $total Result count.
     * @param int $page Zero-based page.
     * @param int $size Rows of a page (as rows() gives them).
     * @param \moodle_url $url Filter-preserving URL.
     * @param string $parameter Independent page parameter.
     * @return string Rendered navigation.
     */
    public static function render(int $total, int $page, int $size, \moodle_url $url, string $parameter = 'page'): string {
        global $OUTPUT;
        $page = self::clamp($total, $page, $size);
        $value = self::value($size, $total);
        $url = new \moodle_url($url, ['perpage' => $value]);
        $data = ['id' => \html_writer::random_id('mp-page-'), 'url' => $url->out(false),
            'pageparameter' => $parameter, 'sizes' => [],
            'summary' => get_string('paginationrange', 'local_filebank', (object)[
                'first' => $total ? $page * $size + 1 : 0, 'last' => min($total, ($page + 1) * $size), 'total' => $total,
            ]),
        ];
        foreach (self::choices($total) as $choice) {
            $data['sizes'][] = ['value' => $choice, 'selected' => $choice === $value,
                'label' => $choice === self::ALL ? get_string('all') : (string) $choice];
        }
        foreach (['previous' => $page - 1, 'next' => $page + 1] as $key => $number) {
            if ($number >= 0 && $number * $size < $total) {
                $data[$key . 'url'] = (new \moodle_url($url, [$parameter => $number]))->out(false);
            }
        }
        return $OUTPUT->render_from_template('local_filebank/local/navigation/pagination', $data);
    }
}
