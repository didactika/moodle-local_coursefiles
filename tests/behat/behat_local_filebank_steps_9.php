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

require_once(__DIR__ . '/local_filebank_context.php');

/**
 * Focused native chooser acceptance steps.
 */
class behat_local_filebank_steps_9 extends behat_local_filebank_base {
    /**
     * Capture the actual embedded picker at desktop and mobile widths.
     *
     * @Then /^I capture the FileBank browser$/
     */
    public function capture_browser(): void {
        $this->wait_for_browser();
        $page = $this->getSession()->getDriver()->evaluateScript(
            'document.querySelector(".fp-object-container").contentDocument'
            . '.querySelector(".filebank-selection-review")?"review":"browser"'
        );
        foreach (['desktop' => 1366, 'mobile' => 480] as $size => $width) {
            $this->getSession()->resizeWindow($width, 900);
            $fits = $this->getSession()->wait(
                3000,
                '(()=>{const doc=document.querySelector(".fp-object-container").contentDocument;'
                . 'return doc.documentElement.scrollWidth<=doc.documentElement.clientWidth+1;})()'
            );
            if (!$fits) {
                throw new \Exception('The embedded chooser overflows the viewport horizontally.');
            }
            $this->getSession()->getDriver()->executeScript(
                'document.querySelector(".fp-object-container").contentDocument.defaultView.scrollTo(0,0);'
            );
            file_put_contents('/tmp/filebank-' . $page . '-' . $size . '.png', $this->getSession()->getScreenshot());
        }
        $this->getSession()->resizeWindow(1366, 1000);
    }

    /**
     * Wait for the serialized small-state response before checking saved decisions.
     * @Then /^I wait for the FileBank migration selection to save$/
     */
    public function migration_selection_saved(): void {
        $this->spin(
            function () {
                return $this->getSession()->evaluateScript(
                    "(function() {const root = document.querySelector('[data-region=filebank-migration]');"
                    . "return root && root.dataset.ready === 'true' && Number(root.dataset.pending || 0) === 0;})()"
                );
            },
            null,
            20
        );
    }

    /**
     * Remember the real DOM node before a selection-only request.
     * @When /^I remember the visible FileBank migration item$/
     */
    public function remember_migration_item(): void {
        $this->getSession()->executeScript(
            'window.filebankRememberedItem = document.querySelector("[data-filebank-operation]");'
        );
    }

    /**
     * Selection-only responses must not replace the visible files.
     * @Then /^the visible FileBank migration item should be preserved$/
     */
    public function migration_item_preserved(): void {
        if (
            !$this->getSession()->evaluateScript(
                'window.filebankRememberedItem === document.querySelector("[data-filebank-operation]")'
            )
        ) {
            throw new \Exception('Selecting a file replaced its visible DOM node.');
        }
    }

    /**
     * Migration bulk deletion is available only for an explicit current-page selection.
     * @Then /^FileBank migration bulk actions should be "(hidden|visible)"$/
     * @param string $expected Expected visibility.
     */
    public function migration_bulk_actions(string $expected): void {
        $control = $this->getSession()->getPage()->find('css', '[data-filebank-migrations] [data-filebank-bulk-actions]');
        if (!$control || $control->isVisible() !== ($expected === 'visible')) {
            throw new \Exception('Migration bulk actions do not follow the explicit selection.');
        }
    }
}
