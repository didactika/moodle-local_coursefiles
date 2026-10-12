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
class behat_local_filebank_steps_6 extends behat_local_filebank_base {
    /**
     * Change the rendered layout without navigating or losing checks.
     *
     * @When /^I switch FileBank layout to "(grid|list|tree)"$/
     * @param string $layout Requested layout.
     */
    public function switch_layout(string $layout): void {
        $this->wait_for_browser();
        $this->getSession()->getDriver()->executeScript(
            'document.querySelector(".fp-object-container").contentDocument.querySelector('
            . json_encode('[data-filebank-layout="' . $layout . '"]') . ').click();'
        );
        if (
            !$this->getSession()->wait(
                3000,
                'document.querySelector(".fp-object-container").contentDocument.querySelector("form").dataset.layout==='
                . json_encode($layout)
            )
        ) {
            throw new \Exception('The visible material layout did not change.');
        }
    }

    /**
     * Exercise one canonical selection through real cards, lazy tree leaves and list rows.
     * @Then /^FileBank selections should stay consistent across all views$/
     */
    public function shared_view_selection(): void {
        $this->select_material('Published materials', 'CB2025');
        $this->execute('behat_local_filebank_steps_8::selected_file_count', [2]);
        $this->switch_layout('tree');
        $this->getSession()->getDriver()->executeScript(
            'const doc=document.querySelector(".fp-object-container").contentDocument;'
            . 'const root=[...doc.querySelectorAll("[data-tree-folder]")].find(folder=>'
            . 'folder.querySelector(".filebank-origin").textContent.startsWith("CB2025"));'
            . 'window.filebankViewRoot=root;root.querySelector("[name=openfolder]").click();'
        );
        if (!$this->getSession()->wait(10000, 'window.filebankViewRoot.querySelector("[data-tree-entries]").dataset.loaded')) {
            throw new \Exception('The selected native tree folder did not expand.');
        }
        $this->execute('behat_local_filebank_steps_8::deselect_source_file', ['lesson.txt']);
        $this->execute('behat_local_filebank_steps_8::selected_file_count', [1]);
        $this->switch_layout('list');
        $correct = $this->getSession()->getDriver()->evaluateScript(
            '(()=>{const doc=document.querySelector(".fp-object-container").contentDocument;'
            . 'const root=window.filebankViewRoot,check=root.querySelector("[data-filebank-choice]");'
            . 'return root.isConnected&&check.indeterminate&&!check.checked&&'
            . '[...doc.querySelectorAll("[data-filebank-course-toggle]")].every(heading=>'
            . 'heading.hidden&&heading.classList.contains("d-none")&&!heading.getClientRects().length);})()'
        );
        if (!$correct) {
            throw new \Exception('List rows exposed course headers or lost the tree partial selection.');
        }
        $this->select_material('Published materials', 'CB2025');
        $this->execute('behat_local_filebank_steps_8::selected_file_count', [2]);
        $this->switch_layout('grid');
        $this->execute('behat_local_filebank_steps_4::native_browser_layouts', []);
        $this->execute('behat_local_filebank_steps_8::selected_file_count', [2]);
        $this->execute('behat_local_filebank_steps_9::capture_browser', []);
    }

    /**
     * Clear an in-flight search while changing layouts, preserving profile and actual selected files.
     * @Then /^FileBank should clear pending filters without losing its view or selection$/
     */
    public function clear_pending_filters(): void {
        $this->select_material('Published materials', 'CB2025');
        $this->execute('behat_local_filebank_steps_8::selected_file_count', [2]);
        $this->getSession()->getDriver()->executeScript(
            'const doc=document.querySelector(".fp-object-container").contentDocument,form=doc.querySelector("form");'
            . 'window.filebankResetState={form,profile:form.elements.profileid.value,busy:false};'
            . 'doc.defaultView.require(["core/fragment"],module=>{const fragment=module.default||module;'
            . 'const load=fragment.loadFragment.bind(fragment);fragment.loadFragment=(...args)=>'
            . 'new Promise((resolve,reject)=>setTimeout(()=>load(...args).then(resolve,reject),500));'
            . 'const input=form.elements.q;input.value="no-such-file";'
            . 'input.dispatchEvent(new Event("input",{bubbles:true}));setTimeout(()=>{'
            . 'window.filebankResetState.busy=form.getAttribute("aria-busy")==="true";'
            . 'form.querySelector("[data-filebank-layout=tree]").click();'
            . 'form.querySelector("[data-filebank-layout=list]").click();'
            . 'form.querySelector("[data-filebank-options]").open=true;'
            . 'form.querySelector("[data-filebank-clear-filters]").click();'
            . 'window.filebankResetState.choicesblocked=[...form.querySelectorAll("[data-filebank-choice]")]'
            . '.every(choice=>choice.disabled);},650);});'
        );
        if (
            !$this->getSession()->wait(
                15000,
                'window.filebankResetState.busy&&window.filebankResetState.form.elements.q.value===""'
                . '&&window.filebankResetState.choicesblocked'
                . '&&window.filebankResetState.form.querySelector("[data-filebank-filter-count]").textContent.trim()===""'
                . '&&window.filebankResetState.form.getAttribute("aria-busy")!=="true"'
            )
        ) {
            throw new \Exception('Clearing the pending search did not apply unrestricted results.');
        }
        $correct = $this->getSession()->getDriver()->evaluateScript(
            '(()=>{const state=window.filebankResetState,form=state.form;'
            . 'return form.isConnected&&form.dataset.layout==="list"&&form.elements.layout.value==="list"'
            . '&&form.elements.profileid.value===state.profile'
            . '&&form.querySelectorAll("[data-filebank-layout].active").length===1'
            . '&&form.querySelector("[data-filebank-layout=list]").getAttribute("aria-pressed")==="true"'
            . '&&form.querySelector("[data-filebank-clear-filters]").disabled'
            . '&&[...form.querySelectorAll("[data-filebank-filter]:not([name=profileid])")].every(input=>input.value==="")'
            . '&&[...form.querySelectorAll("[data-filebank-course-toggle]")].every(heading=>'
            . 'heading.hidden&&!heading.getClientRects().length);})()'
        );
        if (!$correct) {
            throw new \Exception('Layout, active native view, filter reset or owning profile became inconsistent.');
        }
        $this->execute('behat_local_filebank_steps_8::selected_file_count', [2]);
        $this->execute('behat_local_filebank_steps_9::capture_browser', []);
    }

    /**
     * Select one visible source card by title and course.
     *
     * @When /^I select FileBank material "([^"]*)" from "([^"]*)"$/
     * @param string $title Visible file or folder title.
     * @param string $course Source shortname.
     */
    public function select_material(string $title, string $course): void {
        $this->wait_for_browser();
        $script = 'const doc=document.querySelector(".fp-object-container").contentDocument;'
            . 'const card=[...doc.querySelectorAll(".card-body")].find(card=>'
            . 'card.querySelector("label").textContent.trim()===' . json_encode($title)
            . '&&card.querySelector(".filebank-origin").textContent.startsWith(' . json_encode($course) . '));'
            . 'if(!card){throw new Error("Source card unavailable");}'
            . 'card.querySelector(".filebank-origin").click();'
            . 'if(!card.querySelector("input[type=checkbox]").checked){throw new Error("Whole-card selection failed");}';
        $this->getSession()->getDriver()->executeScript($script);
    }

    /**
     * Press an embedded action using the real HTML form or native chooser completion.
     *
     * @When /^I press FileBank action "([^"]*)"$/
     * @param string $label Action text.
     */
    public function press_action(string $label): void {
        $this->wait_for_browser();
        $ready = $this->getSession()->wait(
            10000,
            '[...document.querySelector(".fp-object-container").contentDocument.querySelectorAll("button")]'
            . '.some(button=>button.textContent.trim()===' . json_encode($label) . '&&!button.disabled)'
        );
        if (!$ready) {
            throw new \Exception('Expected browser action was not ready: ' . $label);
        }
        $this->getSession()->getDriver()->executeScript(
            'const doc=document.querySelector(".fp-object-container").contentDocument;'
            . 'if(doc.querySelector("form [name=scope]")){window.filebankTestScope=doc.querySelector("form '
                . '[name=scope]").value;}'
            . 'window.filebankPreviousDocument=doc;window.filebankPreviousForm=doc.querySelector(".file'
                . 'bank-browser");const button=[...doc.querySelectorAll("button")]'
            . '.find(button=>button.textContent.trim()==='
            . json_encode($label) . ');if(!button){throw new Error("Action unavailable");}button.click();'
        );
        if (in_array($label, ['Search materials', 'Review selection'], true)) {
            $this->getSession()->wait(
                10000,
                'document.querySelector(".fp-object-container")?.contentDocument?.querySelector(".filebank-br'
                . 'owser")!==window.filebankPreviousForm'
                . '&&document.querySelector(".fp-object-container")?.contentDocument?.readyState==="complete"'
            );
        }
        if ($label === 'Import materials') {
            $this->getSession()->wait(
                10000,
                '!document.querySelector(".file-picker") || document.querySelector(".file-picker").offsetParent===null'
            );
        }
    }

    /**
     * Check the actual rendered review, including native source declarations.
     *
     * @Then /^the FileBank review should include "([^"]*)"$/
     * @param string $text Expected review text.
     */
    public function review_includes(string $text): void {
        $this->wait_for_browser();
        $found = $this->getSession()->wait(
            10000,
            'document.querySelector(".fp-object-container")?.contentDocument?.querySelector(".filebank-se'
                . 'lection-review")'
            . '&&(()=>{const doc=document.querySelector(".fp-object-container").contentDocument;'
            . 'return doc.querySelector(".filebank-selection-review").textContent.includes(' . json_encode($text) . ')'
            . '||[...doc.querySelectorAll("input[name^=renames]")].some(input=>input.value.includes('
            . json_encode($text) . '));})()'
        );
        if (!$found) {
            throw new \Exception('Expected reviewed material was not rendered: ' . $text);
        }
    }
}
