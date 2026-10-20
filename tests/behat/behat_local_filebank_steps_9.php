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
     * Capture the part of an administration page that holds an element, at desktop and mobile widths.
     *
     * @Then /^I capture FileBank administration "([^"]*)" at "([^"]*)"$/
     * @param string $name Screenshot basename.
     * @param string $selector CSS selector of the element to bring into view.
     */
    public function capture_administration_at(string $name, string $selector): void {
        foreach (['desktop' => 1366, 'mobile' => 480] as $size => $width) {
            $this->getSession()->resizeWindow($width, 1000);
            $this->getSession()->getDriver()->executeScript(
                'document.querySelector(' . json_encode($selector) . ').scrollIntoView({block: "start"});'
                . 'window.scrollBy(0, -80);'
            );
            file_put_contents('/tmp/filebank-admin-' . $name . '-' . $size . '.png', $this->getSession()->getScreenshot());
        }
        $this->getSession()->resizeWindow(1366, 1000);
    }

    /**
     * Go through the steps of the page that starts a migration with one correspondence, and stop at the check of the last step.
     *
     * @When /^I set up a FileBank migration of "([^"]*)" into "([^"]*)" from "([^"]*)" to "([^"]*)"$/
     * @param string $source Exact name of the source folder.
     * @param string $target Exact name of the destination folder.
     * @param string $from Edition of the source courses.
     * @param string $to Edition of the destination courses.
     */
    public function set_up_migration(string $source, string $target, string $from, string $to): void {
        $click = static fn(string $selector): array => [$selector, 'css_element'];
        $this->execute('behat_general::i_click_on', $click("[data-action='new-migration']"));
        $this->execute('behat_general::i_click_on', $click("[data-action='next-step']"));
        $this->execute('behat_forms::i_set_the_field_to', ['fbm-source-edition', $from]);
        $this->execute('behat_forms::i_set_the_field_to', ['fbm-target-edition', $to]);
        $this->execute('behat_general::i_click_on', $click("[data-action='next-step']"));
        $this->execute('behat_general::i_click_on', $click("[data-action='add-correspondence']"));
        $this->execute('behat_forms::i_set_the_field_to', ['fbm-c0-source-value', $source]);
        $this->execute('behat_forms::i_set_the_field_to', ['fbm-c0-target-value', $target]);
        // Folders, sections and the policies each take one click to leave, and the last one opens the check.
        for ($step = 0; $step < 3; $step++) {
            $this->execute('behat_general::i_click_on', $click("[data-action='next-step']"));
        }
        $result = "[data-region='check-result'],[data-region='empty-state']";
        $this->execute('behat_general::wait_until_exists', [$result, 'css_element']);
    }
}
