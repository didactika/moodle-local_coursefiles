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
 * FileBank native material import integration.
 *
 * @package local_filebank
 * @copyright 2026 Didactika.org
 * @author Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_filebank\local;

/**
 * Decode bounded native Fragment requests in the system context.
 */
final class fragment_request {
    /**
     * State.
     *
     * @param array $args Args.
     * @param int $maxbytes Maxbytes.
     * @param int $depth Depth.
     * @return array Result.
     */
    public static function state(array $args, int $maxbytes, int $depth): array {
        if (
            $args['context']->id !== \context_system::instance()->id || !is_string($args['state'] ?? null)
                || strlen($args['state']) > $maxbytes
        ) {
            throw new \invalid_parameter_exception('Invalid fragment request');
        }
        try {
            $state = json_decode($args['state'], true, $depth, JSON_THROW_ON_ERROR);
        } catch (\JsonException $exception) {
            throw new \invalid_parameter_exception('Invalid fragment document');
        }
        if (!is_array($state) || !confirm_sesskey($state['sesskey'] ?? '')) {
            throw new \moodle_exception('invalidsesskey');
        }
        return $state;
    }
}
