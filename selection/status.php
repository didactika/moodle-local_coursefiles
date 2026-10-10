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

define('AJAX_SCRIPT', true);
require_once(__DIR__ . '/../../../config.php');

use local_filebank\local\selection\draft_scope;

$token = required_param('scope', PARAM_ALPHANUM);
require_login();
require_sesskey();
$scope = draft_scope::describe($token, sesskey());
$PAGE->set_context(context::instance_by_id($scope['contextid']));
$files = [];
$storage = get_file_storage();
$context = context_user::instance($USER->id);
$existing = null;
foreach ($scope['imported'] ?? [] as $operation) {
    $file = $storage->get_file($context->id, 'user', 'draft', $scope['draftid'], $operation['filepath'], $operation['filename']);
    if (!$file) {
        $existing ??= $storage->get_area_files($context->id, 'user', 'draft', $scope['draftid'], 'id', false);
        foreach ($existing as $candidate) {
            if ($candidate->get_contenthash() === $operation['contenthash']) {
                $file = $candidate;
                break;
            }
        }
    }
    if ($file) {
        $files[$file->get_pathnamehash()] = ['name' => $file->get_filename(),
            'url' => moodle_url::make_draftfile_url($scope['draftid'], $file->get_filepath(), $file->get_filename())->out(false)];
    }
}
echo $OUTPUT->header();
echo json_encode(['ready' => isset($scope['imported']), 'links' => $scope['editorlinks'] ?? false,
    'files' => array_values($files)]);
