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
class behat_local_filebank_steps_7 extends behat_local_filebank_base {
    /**
     * Set a visible browser filter and retain the native HTML form submission.
     *
     * @When /^I set FileBank filter "([^"]*)" to "([^"]*)"$/
     * @param string $name Filter field name.
     * @param string $value Requested filter value.
     */
    public function set_filter(string $name, string $value): void {
        $this->wait_for_browser();
        $this->getSession()->getDriver()->executeScript(
            'const doc=document.querySelector(".fp-object-container").contentDocument;'
            . 'window.filebankPreviousDocument=doc;window.filebankPreviousForm=doc.querySelector(".file'
                . 'bank-browser");'
            . 'window.filebankPreviousRefresh=doc.querySelector("[data-filebank-results]").dataset.refresh;'
            . 'const field=doc.getElementsByName(' . json_encode($name) . ')[0];'
            . 'field.focus();field.value=' . json_encode($value) . ';field.dispatchEvent(new Event("input",{bubbles:true}));'
            . 'field.dispatchEvent(new Event("change",{bubbles:true}));'
        );
        if (
            !$this->getSession()->wait(
                10000,
                'document.querySelector(".fp-object-container")?.contentDocument?.querySelector("[data-file'
                . 'bank-results]").dataset.refresh!==window.filebankPreviousRefresh'
                . '&&document.querySelector(".fp-object-container")?.contentDocument?.readyState==="complete"'
            )
        ) {
            $visible = $this->getSession()->getDriver()->evaluateScript(
                'document.querySelector(".fp-object-container").contentDocument.body.innerText'
            );
            throw new \Exception('Changing the filter did not update the visible materials automatically: ' . $visible);
        }
        $this->wait_for_browser();
        if ($name === 'q') {
            $retained = $this->getSession()->getDriver()->evaluateScript(
                '(()=>{const doc=document.querySelector(".fp-object-container").contentDocument;'
                . 'return doc===window.filebankPreviousDocument&&doc.activeElement?.name==="q";})()'
            );
            if (!$retained) {
                throw new \Exception('Automatic search reloaded the document or lost the search field focus.');
            }
        }
    }

    /**
     * Count the visible file cards, independent of synthetic folder cards.
     *
     * @Then /^I should see "([0-9]+)" FileBank file cards named "([^"]*)"$/
     * @param int $count Expected matching visible file cards.
     * @param string $title File name.
     */
    public function file_card_count(int $count, string $title): void {
        $this->wait_for_browser();
        $found = $this->getSession()->wait(
            10000,
            '[...document.querySelector(".fp-object-container").contentDocument.querySelectorAll(".card-body")]'
            . '.filter(card=>card.querySelector("label").textContent.trim()===' . json_encode($title)
            . '&&card.querySelector("input").value.startsWith("file:")).length===' . $count
        );
        if (!$found) {
            throw new \Exception('Unexpected number of authorized file cards.');
        }
    }

    /**
     * Verify hidden or filtered origins are absent from the rendered browser.
     *
     * @Then /^the FileBank browser should not include "([^"]*)"$/
     * @param string $text Excluded visible text.
     */
    public function browser_excludes(string $text): void {
        $this->wait_for_browser();
        $found = $this->getSession()->wait(
            10000,
            '(()=>{const doc=document.querySelector(".fp-object-container").contentDocument;'
            . 'return !doc.querySelector(".filebank-browser").textContent.includes(' . json_encode($text) . ')'
            . '&&![...doc.querySelectorAll("input[name^=renames]")].some(input=>input.value.includes('
            . json_encode($text) . '));})()'
        );
        if (!$found) {
            throw new \Exception('An unavailable or filtered origin was shown: ' . $text);
        }
    }

    /**
     * Deny activity settings while retaining the native Folder content permission.
     *
     * @Given /^the FileBank teacher can only manage folder content$/
     */
    public function restrict_folder_teacher(): void {
        global $DB;
        $course = $DB->get_record('course', ['shortname' => 'CB2026'], '*', MUST_EXIST);
        $roles = get_archetype_roles('editingteacher');
        assign_capability(
            'moodle/course:manageactivities',
            CAP_PROHIBIT,
            reset($roles)->id,
            \context_course::instance($course->id)->id
        );
        accesslib_clear_all_caches(true);
    }

    /**
     * Remove a resulting file after its source card was filtered out.
     *
     * @When /^I remove FileBank reviewed file "([^"]*)"$/
     * @param string $filename Reviewed destination name.
     */
    public function remove_reviewed_file(string $filename): void {
        $this->execute('behat_local_filebank_steps_6::press_action', ['Review selection']);
        $this->wait_for_browser();
        $this->getSession()->getDriver()->executeScript(
            'const doc=document.querySelector(".fp-object-container").contentDocument;'
            . 'window.filebankPreviousForm=doc.querySelector(".filebank-browser");'
            . 'const input=[...doc.querySelectorAll("input[name^=renames]")].find(input=>input.value==='
            . json_encode($filename) . ');input.closest(".filebank-tree-file").querySelector("button[name=removeitem]").click();'
        );
        $this->getSession()->wait(
            10000,
            'document.querySelector(".fp-object-container")?.contentDocument?.querySelector(".filebank-br'
                . 'owser")!==window.filebankPreviousForm'
        );
        $this->wait_for_browser();
        $this->execute('behat_local_filebank_steps_5::back_to_selection', []);
    }

    /**
     * Navigate an actual source folder without changing its selection.
     *
     * @When /^I open FileBank folder "([^"]*)" from "([^"]*)"$/
     * @param string $name Folder label.
     * @param string $course Source shortname.
     */
    public function open_source_folder(string $name, string $course): void {
        $this->wait_for_browser();
        $this->getSession()->getDriver()->executeScript(
            'const doc=document.querySelector(".fp-object-container").contentDocument;'
            . 'window.filebankPreviousForm=doc.querySelector(".filebank-browser");'
            . 'const card=[...doc.querySelectorAll(".card-body")].find(card=>'
            . 'card.querySelector("label").textContent.trim()===' . json_encode($name)
            . '&&card.querySelector(".filebank-origin")?.textContent.startsWith(' . json_encode($course) . '));'
            . 'card.querySelector(".filebank-open").click();'
        );
        $this->getSession()->wait(
            10000,
            'document.querySelector(".fp-object-container")?.contentDocument?.querySelector(".filebank-browser")'
            . '!==window.filebankPreviousForm'
        );
        $this->wait_for_browser();
    }
}
