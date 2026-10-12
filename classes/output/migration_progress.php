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
 * Paged migration review presentation.
 *
 * @package local_filebank
 * @copyright 2026 Didactika.org
 * @author Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_filebank\output;

/**
 * Format plan progress and independent operation/issue pagination controls.
 */
final class migration_progress {
    /**
     * Apply.
     *
     * @param array $data Data.
     * @param \moodle_url $url Url.
     * @param array $context Context.
     */
    public static function apply(array &$data, \moodle_url $url, array $context): void {

        $issuecount = $context['issuecount'];
        $issuepage = $context['issuepage'];
        $perpage = $context['perpage'];
        $decision = $context['decision'];
        $page = $context['page'];
        $search = $context['search'];
        $completed = $context['completed'];
        $includedtotal = $context['includedtotal'];
        $filtered = $context['filtered'];
        self::issues($data, $url, $context);
        $data['hasruns'] = (bool)$data['runs'];
        $data['hasoperations'] = (bool)$data['groups'];
        $data['plancompleted'] = $completed;
        $data['plantotal'] = max(1, $includedtotal);
        $data['planprogress'] = get_string(
            'progresslabel',
            'local_filebank',
            (object)['done' => $completed, 'total' => $includedtotal]
        );
        $data['pageinfo'] = get_string(
            'reviewpage',
            'local_filebank',
            (object)['page' => $page + 1, 'pages' => max(1, (int)ceil($filtered / $perpage)), 'total' => $filtered]
        );
        foreach (['previousurl' => $page - 1, 'nexturl' => $page + 1] as $name => $number) {
            if ($number >= 0 && $number * $perpage < $filtered) {
                $data[$name] = (new \moodle_url(
                    $url,
                    [
                        'decision' => $decision, 'page' => $number, 'q' => $search, 'issuepage' => $issuepage,
                    ]
                ))->out(false);
            }
        }
        $data['allchecked'] = $data['total'] > 0 && $data['included'] === $data['total'];
        $data['allpartial'] = $data['included'] > 0 && $data['included'] < $data['total'];
        $data['pagination'] = pagination::render(
            $filtered,
            $page,
            $perpage,
            new \moodle_url($url, ['decision' => $decision, 'q' => $search, 'issuepage' => $issuepage])
        );
        $data['issuepagination'] = pagination::render(
            $issuecount,
            $issuepage,
            $perpage,
            new \moodle_url($url, ['decision' => $decision, 'q' => $search, 'page' => $page]),
            'issuepage'
        );
    }

    /**
     * Issues.
     *
     * @param array $data Data.
     * @param \moodle_url $url Url.
     * @param array $context Context.
     */
    private static function issues(array &$data, \moodle_url $url, array $context): void {
        $issuecount = $context['issuecount'];
        $issuepage = $context['issuepage'];
        $perpage = $context['perpage'];
        $decision = $context['decision'];
        $page = $context['page'];
        $search = $context['search'];
        $execution = $context['execution'];
        $data['hasissues'] = $issuecount > 0;
        foreach (['issuepreviousurl' => $issuepage - 1, 'issuenexturl' => $issuepage + 1] as $name => $number) {
            if ($number >= 0 && $number * $perpage < $issuecount) {
                $data[$name] = (new \moodle_url(
                    $url,
                    [
                        'decision' => $decision, 'page' => $page, 'q' => $search, 'issuepage' => $number,
                        'step' => $execution ? 'execute' : '',
                    ]
                ))->out(false);
            }
        }
    }
}
