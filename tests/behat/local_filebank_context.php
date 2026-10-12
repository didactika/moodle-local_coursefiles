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

require_once(__DIR__ . '/../../../../lib/behat/behat_base.php');

/**
 * Shared native chooser readiness helpers.
 */
abstract class behat_local_filebank_base extends behat_base {
    /**
     * Await the real embedded document after native AJAX listing and form navigation.
     */
    protected function wait_for_browser(): void {
        $ready = $this->getSession()->wait(
            10000,
            'document.querySelector(".fp-object-container")?.contentDocument?.readyState==="complete"'
            . '&&document.querySelector(".fp-object-container").contentDocument.querySelector(".filebank-br'
                . 'owser")?.dataset.filebankReady==="true"'
            . '&&document.querySelector(".fp-object-container").contentDocument.querySelector(".filebank-br'
                . 'owser")?.getAttribute("aria-busy")!=="true"'
        );
        if (!$ready) {
            throw new \Exception('The native FileBank browser did not load.');
        }
    }
}
