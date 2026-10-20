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

    /** @var int The most rows that "All" shows, so a long list never loads at once. */
    public const MAX_ALL = 500;

    /**
     * Read a supported page size: one of the sizes, or "All" (a number up to the bound).
     * @param int $default Default size.
     * @return int Page size.
     */
    public static function size(int $default = 12): int {
        $size = optional_param('perpage', $default, PARAM_INT);
        return in_array($size, self::SIZES, true) || ($size > 0 && $size <= self::MAX_ALL) ? $size : $default;
    }

    /**
     * The sizes to offer for a list: those smaller than the list and the whole list, when it is not too long.
     * @param int $total Result count.
     * @return int[] The sizes.
     */
    public static function choices(int $total): array {
        if ($total <= self::SIZES[0]) {
            return [max(1, $total)];
        }
        $sizes = array_values(array_filter(self::SIZES, static fn(int $size): bool => $size < $total));
        return $total <= self::MAX_ALL ? array_merge($sizes, [$total]) : $sizes;
    }

    /**
     * Clamp a page against the filtered result count.
     * @param int $total Result count.
     * @param int $page Requested zero-based page.
     * @param int $size Page size.
     * @return int Valid page.
     */
    public static function clamp(int $total, int $page, int $size): int {
        return max(0, min($page, max(0, (int)ceil($total / $size) - 1)));
    }

    /**
     * Render themeable range, native page links and a bounded size selector.
     * @param int $total Result count.
     * @param int $page Zero-based page.
     * @param int $size Page size.
     * @param \moodle_url $url Filter-preserving URL.
     * @param string $parameter Independent page parameter.
     * @return string Rendered navigation.
     */
    public static function render(int $total, int $page, int $size, \moodle_url $url, string $parameter = 'page'): string {
        global $OUTPUT;
        $page = self::clamp($total, $page, $size);
        $url = new \moodle_url($url, ['perpage' => $size]);
        $data = ['id' => \html_writer::random_id('mp-page-'), 'url' => $url->out(false),
            'pageparameter' => $parameter, 'sizes' => [],
            'summary' => get_string('paginationrange', 'local_filebank', (object)[
                'first' => $total ? $page * $size + 1 : 0, 'last' => min($total, ($page + 1) * $size), 'total' => $total,
            ]),
        ];
        foreach (self::choices($total) as $value) {
            $data['sizes'][] = ['value' => $value, 'selected' => $value === $size,
                'label' => $value === $total ? get_string('all') : (string) $value];
        }
        foreach (['previous' => $page - 1, 'next' => $page + 1] as $key => $number) {
            if ($number >= 0 && $number * $size < $total) {
                $data[$key . 'url'] = (new \moodle_url($url, [$parameter => $number]))->out(false);
            }
        }
        return $OUTPUT->render_from_template('local_filebank/local/navigation/pagination', $data);
    }
}
