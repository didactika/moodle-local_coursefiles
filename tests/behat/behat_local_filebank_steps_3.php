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
class behat_local_filebank_steps_3 extends behat_local_filebank_base {
    /**
     * Verify actual visibility, compact labels and native action icons.
     *
     * @Then /^FileBank profile bulk actions should be "(hidden|visible)"$/
     * @param string $state Expected visibility.
     */
    public function profile_bulk_actions(string $state): void {
        $visible = $state === 'visible' ? 'true' : 'false';
        $correct = $this->getSession()->getDriver()->evaluateScript(
            '(()=>{const bar=document.querySelector("[data-filebank-bulk-actions]");'
            . 'const buttons=[...bar.querySelectorAll("button")];'
            . 'return (bar.getClientRects().length>0)===' . $visible . '&&buttons.length===4'
            . '&&buttons.every(button=>button.textContent.trim()!==""&&button.getAttribute("title")'
            . '&&button.getAttribute("aria-label")&&button.querySelector(".icon"));})()'
        );
        if (!$correct) {
            throw new \Exception('Profile bulk actions do not match the selected state or native icon contract.');
        }
    }

    /**
     * Reconfiguration keeps the initial reviewed record intact.
     *
     * @Then /^FileBank should retain "([0-9]+)" migration plans$/
     * @param string $count Expected stored plans.
     */
    public function retained_migration_plans(string $count): void {
        global $DB;
        if ($DB->count_records('local_filebank_plan') !== (int)$count) {
            throw new \Exception('Migration reconfiguration replaced its original plan.');
        }
    }

    /**
     * Exercise translated native schemas and profile submissions.
     *
     * @Given /^the FileBank administrator uses Spanish$/
     */
    public function administrator_uses_spanish(): void {
        global $DB, $CFG;
        require_once($CFG->libdir . '/componentlib.class.php');
        make_upload_directory('lang');
        (new lang_installer('es'))->run();
        get_string_manager(true)->reset_caches();
        if (!get_string_manager()->translation_exists('es')) {
            throw new \Exception('The official Spanish language pack is required for this scenario.');
        }
        $DB->set_field('user', 'lang', 'es', ['id' => get_admin()->id]);
        set_config('lang', 'es');
        $CFG->lang = 'es';
    }

    /**
     * Required markers and fieldsets keep the same native input column.
     *
     * @Then /^the FileBank profile inputs should align$/
     */
    public function profile_inputs_align(): void {
        $valid = $this->getSession()->getDriver()->evaluateScript(
            '(()=>{const names=["profilename","editmode","coursefield","coursepattern","familytemplate",'
            . '"displaytemplate","pairdimensions"];'
            . 'const left=names.map(name=>document.getElementById("id_"+name).getBoundingClientRect().left);'
            . 'return Math.max(...left)-Math.min(...left)<=2;})()'
        );
        if (!$valid) {
            $positions = $this->getSession()->getDriver()->evaluateScript(
                'JSON.stringify(["profilename","editmode","coursefield","coursepattern","familytemplate",'
                . '"displaytemplate","pairdimensions"].map(name=>{'
                . 'const input=document.getElementById("id_"+name);return {name,left:input.getBoundingClientRect().left,'
                . 'width:input.getBoundingClientRect().width,display:getComputedStyle(input).display};}))'
            );
            throw new \Exception('Profile inputs do not share the same native column: ' . $positions);
        }
    }

    /**
     * A native folder checkbox must update state without replacing result nodes.
     *
     * @When /^I select a FileBank card without rebuilding its results$/
     */
    public function select_without_rebuilding(): void {
        $this->wait_for_browser();
        $this->getSession()->getDriver()->executeScript(
            'const doc=document.querySelector(".fp-object-container").contentDocument;'
            . 'const results=doc.querySelector("[data-filebank-results]");results.style.maxHeight="60px";'
            . 'results.scrollTop=results.scrollHeight;'
            . 'window.filebankStableSelection={cards:[...results.querySelectorAll(".filebank-card")],'
            . 'scroll:results.scrollTop,refresh:results.dataset.refresh||"0"};'
            . 'const choice=[...results.querySelectorAll("button[name=openfolder]")]'
            . '.find(button=>button.closest(".filebank-card").textContent.includes("Published materials"))'
            . '.closest(".filebank-card").querySelector("input");choice.click();'
        );
        if (
            !$this->getSession()->wait(
                15000,
                'document.querySelector(".fp-object-container").contentDocument.querySelector("[data-filebank-results]")'
                . '.dataset.refresh!==window.filebankStableSelection.refresh'
            )
        ) {
            throw new \Exception('The native selection request did not complete.');
        }
        $this->wait_for_browser();
        if (
            !$this->getSession()->getDriver()->evaluateScript(
                '(()=>{const doc=document.querySelector(".fp-object-container").contentDocument;'
                . 'const state=window.filebankStableSelection;return state.cards.every(card=>card.isConnected)'
                . '&&doc.querySelector("[data-filebank-results]").scrollTop===state.scroll;})()'
            )
        ) {
            throw new \Exception('Selecting a folder replaced result cards or changed the scroll position.');
        }
    }
}
