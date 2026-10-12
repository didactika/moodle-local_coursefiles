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
class behat_local_filebank_steps_8 extends behat_local_filebank_base {
    /**
     * Deselect one real file after recursively selecting its enclosing folder.
     *
     * @When /^I deselect FileBank file "([^"]*)"$/
     * @param string $name Source file label.
     */
    public function deselect_source_file(string $name): void {
        $this->wait_for_browser();
        $this->getSession()->getDriver()->executeScript(
            'const doc=document.querySelector(".fp-object-container").contentDocument;'
            . 'const card=[...doc.querySelectorAll(".card-body")].find(card=>'
            . 'card.querySelector("label").textContent.trim()===' . json_encode($name) . ');'
            . 'const check=card.querySelector("input[type=checkbox]");'
            . 'if(!check.checked){throw new Error("Recursive file selection was lost");}'
            . 'check.click();'
        );
    }

    /**
     * Count resolved files rather than source folder locators.
     *
     * @Then /^FileBank should have "([0-9]+)" selected files$/
     * @param int $count Expected resolved files.
     */
    public function selected_file_count(int $count): void {
        if (
            !$this->getSession()->wait(
                10000,
                'Number(document.querySelector(".fp-object-container")?.contentDocument'
                . '?.querySelector("[data-filebank-count]")?.textContent)===' . $count
            )
        ) {
            throw new \Exception('The recursive file count is incorrect.');
        }
        $this->wait_for_browser();
    }

    /**
     * Rename through the visible review input and rebuild server approval.
     *
     * @When /^I rename FileBank reviewed file "([^"]*)" to "([^"]*)"$/
     * @param string $previous Current destination filename.
     * @param string $name New destination filename.
     */
    public function rename_reviewed_file(string $previous, string $name): void {
        $this->wait_for_browser();
        $this->getSession()->getDriver()->executeScript(
            'const doc=document.querySelector(".fp-object-container").contentDocument;'
            . 'window.filebankPreviousForm=doc.querySelector(".filebank-browser");'
            . 'const input=[...doc.querySelectorAll("input[name^=renames]")].find(input=>input.value==='
            . json_encode($previous) . ');input.closest(".filebank-tree-file")'
            . '.querySelector("[data-filebank-rename]").click();'
        );
        if (
            !$this->getSession()->wait(
                10000,
                '!!document.querySelector(".fp-object-container").contentDocument.querySelector("[data-file'
                . 'bank-filename]")'
            )
        ) {
            throw new \Exception('The native rename modal did not open.');
        }
        $this->getSession()->getDriver()->executeScript(
            'const doc=document.querySelector(".fp-object-container").contentDocument;'
            . 'const input=doc.querySelector("[data-filebank-filename]");input.value=' . json_encode($name) . ';'
            . 'input.closest(".modal").querySelector("[data-action=save]").click();'
        );
        $this->getSession()->wait(
            10000,
            'document.querySelector(".fp-object-container")?.contentDocument?.querySelector(".filebank-browser")'
            . '!==window.filebankPreviousForm'
        );
        $this->wait_for_browser();
    }

    /**
     * Use a real native instance name before rendering the owning activity.
     *
     * @Given /^the native FileBank repository is named "([^"]*)"$/
     * @param string $name Repository instance name.
     */
    public function name_repository(string $name): void {
        global $DB;
        $typeid = $DB->get_field('repository', 'id', ['type' => 'filebank'], MUST_EXIST);
        $DB->set_field('repository_instances', 'name', $name, ['typeid' => $typeid]);
    }

    /**
     * Check the same configured name on the native sidebar and embedded heading.
     *
     * @Then /^the FileBank heading should be "([^"]*)"$/
     * @param string $name Configured name.
     */
    public function repository_heading(string $name): void {
        $this->wait_for_browser();
        if (
            !$this->getSession()->getDriver()->evaluateScript(
                'document.querySelector(".fp-object-container").contentDocument.querySelector("h2").textContent.trim()==='
                . json_encode($name)
            )
        ) {
            throw new \Exception('The embedded heading does not match the repository name.');
        }
    }

    /**
     * Continue typing while a real Fragment response is intentionally delayed.
     *
     * @When /^I filter FileBank while its results are loading$/
     */
    public function filter_while_loading(): void {
        $this->wait_for_browser();
        $this->getSession()->getDriver()->executeScript(
            'const doc=document.querySelector(".fp-object-container").contentDocument;'
            . 'const frame=doc.defaultView;window.filebankLiveFilter=null;'
            . 'const height=document.querySelector(".file-picker").getBoundingClientRect().height;'
            . 'frame.require(["core/fragment"],module=>{const fragment=module.default||module;'
            . 'const load=fragment.loadFragment.bind(fragment);fragment.loadFragment=(...args)=>'
            . 'new Promise((resolve,reject)=>setTimeout(()=>load(...args).then(resolve,reject),500));'
            . 'const input=doc.querySelector("input[name=q]");input.focus();input.value="notes";'
            . 'input.dispatchEvent(new Event("input",{bubbles:true}));'
            . 'setTimeout(()=>{window.filebankLiveFilter={input,height,enabled:!input.disabled,'
            . 'focused:doc.activeElement===input,busy:doc.querySelector("form").getAttribute("aria-busy")==="true"};'
            . 'input.value="notes2025";input.dispatchEvent(new Event("input",{bubbles:true}));},650);});'
        );
        if (
            !$this->getSession()->wait(
                15000,
                'window.filebankLiveFilter&&document.querySelector(".fp-object-container").contentDocument'
                . '.querySelector(".filebank-card-title")?.textContent.trim()==="notes2025.txt"'
            )
        ) {
            throw new \Exception('The latest filter text was not applied after the pending response.');
        }
        $this->wait_for_browser();
        $valid = $this->getSession()->getDriver()->evaluateScript(
            '(()=>{const doc=document.querySelector(".fp-object-container").contentDocument;'
            . 'const state=window.filebankLiveFilter;return state.enabled&&state.focused&&state.busy'
            . '&&doc.activeElement===state.input&&state.input.value==="notes2025"'
            . '&&Math.abs(document.querySelector(".file-picker").getBoundingClientRect().height-state.height)<=1;})()'
        );
        if (!$valid) {
            throw new \Exception('Filter input, focus or modal dimensions changed during the refresh.');
        }
    }
}
