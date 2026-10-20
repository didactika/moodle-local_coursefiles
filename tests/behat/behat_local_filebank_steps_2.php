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
class behat_local_filebank_steps_2 extends behat_local_filebank_base {
    /**
     * Count actual restrictions, excluding empty/All choices and copy options.
     * @Then /^FileBank should show "([0-9]+)" active filters$/
     * @param string $count Expected restrictions.
     */
    public function active_filter_count(string $count): void {
        $this->wait_for_browser();
        $expected = (int)$count ? '(' . $count . ')' : '';
        $actual = $this->getSession()->getDriver()->evaluateScript(
            'document.querySelector(".fp-object-container").contentDocument'
            . '.querySelector("[data-filebank-filter-count]").textContent'
        );
        if (trim($actual) !== $expected) {
            throw new \Exception('The active filter count is incorrect: ' . $actual);
        }
    }

    /**
     * Exercise real editable rows, serialization and native form bounds at both widths.
     *
     * @Then /^the FileBank correspondence rows should fit narrow screens$/
     */
    public function responsive_correspondence_rows(): void {

        $this->getSession()->getDriver()->executeScript(
            'const field=document.getElementById("id_correspondencesjson");'
            . 'window.filebankOriginalRows=field.value;'
            . 'field.nextElementSibling.querySelector("[data-filebank-addrow]").click();'
        );
        if (!$this->getSession()->wait(5000, 'document.querySelectorAll("[data-field=targetvalue]").length===2')) {
            throw new \Exception('A second native correspondence row was not added.');
        }
        $this->getSession()->getDriver()->executeScript(
            'const input=document.querySelectorAll("[data-field=targetvalue]")[1];input.value="Temporary destination";'
            . 'input.dispatchEvent(new Event("input",{bubbles:true}));'
        );
        foreach ([1366, 480] as $width) {
            $this->getSession()->resizeWindow($width, 1000);
            $this->close_drawer($width);
            $result = $this->getSession()->getDriver()->evaluateScript(
                '(()=>{const field=document.getElementById("id_correspondencesjson"),wrapper=field.nextElementSibling;'
                . 'const editor=wrapper.querySelector(".filebank-row-editor"),bounds=editor.getBoundingClientRect();'
                . 'editor.scrollIntoView({block:"center"});'
                . 'const controls=[...editor.querySelectorAll("input,select,button")];'
                . 'const fits=controls.every(control=>{const rect=control.getBoundingClientRect();'
                . 'return rect.width>0&&rect.left>=bounds.left-1&&rect.right<=bounds.right+1;});'
                . 'const aligned=[...editor.querySelectorAll("tbody tr")].every(row=>{'
                . 'const fields=[...row.querySelectorAll("[data-field]")];'
                . 'return fields.every(input=>fields.every(other=>'
                . 'Math.abs(input.closest("td").getBoundingClientRect().top-'
                . 'other.closest("td").getBoundingClientRect().top)>2||'
                . 'Math.abs(input.getBoundingClientRect().bottom-other.getBoundingClientRect().bottom)<2));});'
                . 'return JSON.stringify({fits,aligned,'
                . 'page:document.documentElement.scrollWidth-document.documentElement.clientWidth,'
                . 'overflow:editor.scrollWidth-editor.clientWidth,width:innerWidth,'
                . 'saved:JSON.parse(field.value)[1].targetvalue});})()'
            );
            $result = json_decode($result, true);
            if (
                !$result['fits'] || !$result['aligned'] || $result['page'] > 1 || $result['overflow'] > 1
                || $result['saved'] !== 'Temporary destination'
            ) {
                throw new \Exception('Native correspondence controls overflow or lose edits: ' . json_encode($result));
            }
            file_put_contents('/tmp/filebank-correspondences-' . $width . '.png', $this->getSession()->getScreenshot());
        }
        $this->getSession()->getDriver()->executeScript(
            'const field=document.getElementById("id_correspondencesjson");'
            . 'field.nextElementSibling.querySelectorAll("tbody tr")[1].querySelector("button").click();'
            . 'if(field.value!==window.filebankOriginalRows){throw new Error("Original correspondences changed.");}'
        );
        $this->getSession()->resizeWindow(1366, 1000);
    }

    /**
     * Keep the native Back button at the leading edge without theme-specific placement.
     *
     * @Then /^the FileBank history Back action should stay on the left$/
     */
    public function history_back_on_left(): void {
        foreach ([1366, 480] as $width) {
            $this->getSession()->resizeWindow($width, 1000);
            $correct = $this->getSession()->getDriver()->evaluateScript(
                '(()=>{const back=document.querySelector(".filebank-admin .filebank-back");'
                . 'return Math.abs(back.getBoundingClientRect().left-back.parentElement.getBoundingClientRect().left)<2'
                . '&&back.classList.contains("fbm-back")&&!!back.querySelector(".icon");})()'
            );
            if (!$correct) {
                throw new \Exception('Profile history Back does not follow the shared native leading-edge action.');
            }
        }
        $this->getSession()->resizeWindow(1366, 1000);
    }

    /**
     * Open the oldest retained configuration without assuming generated database IDs.
     * @When /^I compare the oldest FileBank profile version$/
     */
    public function compare_oldest_profile_version(): void {
        $this->getSession()->getDriver()->executeScript(
            'const checks=[...document.querySelectorAll("[data-filebank-version]")];'
            . 'if(checks.length<2){throw new Error("Profile versions are missing.");}'
            . 'checks[0].click();checks.at(-1).click();'
            . 'document.querySelector("#filebank-compare button").click();'
        );
    }

    /**
     * Check the real theme's clamping, card row alignment and centered navigation.
     * @Then /^FileBank cards and filters should keep a compact aligned layout$/
     */
    public function compact_browser_layout(): void {
        $this->wait_for_browser();
        $correct = $this->getSession()->getDriver()->evaluateScript(
            '(()=>{const doc=document.querySelector(".fp-object-container").contentDocument;'
            . 'const cards=[...doc.querySelectorAll(".filebank-card")];'
            . 'const aligned=cards.every(card=>{const name=card.querySelector(".filebank-card-title");'
            . 'return name.title&&doc.defaultView.getComputedStyle(name).webkitLineClamp==="2";});'
            . 'const button=doc.querySelector(".filebank-open"),icon=button.querySelector(".icon");'
            . 'const b=button.getBoundingClientRect(),i=icon.getBoundingClientRect();'
            . 'const centered=Math.abs(b.x+b.width/2-i.x-i.width/2)<2'
            . '&&Math.abs(b.y+b.height/2-i.y-i.height/2)<2;'
            . 'const toggle=doc.querySelector("[data-filebank-options]");toggle.open=true;'
            . 'const menu=toggle.querySelector(".filebank-filter-menu");'
            . 'const bounded=menu.getBoundingClientRect().height<=353'
            . '&&doc.defaultView.getComputedStyle(menu).overflowY==="auto";'
            . 'const input=doc.querySelector("#mpq"),summary=toggle.querySelector("summary");'
            . 'const filtersAligned=Math.abs(input.getBoundingClientRect().bottom'
            . '-summary.getBoundingClientRect().bottom)<2;'
            . 'toggle.open=false;return aligned&&centered&&bounded&&filtersAligned;})()'
        );
        if (!$correct) {
            throw new \Exception('Native card names, folder navigation or filter menu do not follow the compact layout.');
        }
    }

    /**
     * Exercise whole-row checks without disabling native transfer approval.
     * @Then /^the FileBank review should use compact selectable rows$/
     */
    public function compact_review_rows(): void {
        $this->wait_for_browser();
        $correct = $this->getSession()->getDriver()->evaluateScript(
            '(()=>{const doc=document.querySelector(".fp-object-container").contentDocument;'
            . 'const row=doc.querySelector(".filebank-tree-file"),check=row.querySelector("input[type=checkbox]");'
            . 'row.querySelector("[data-filebank-file-name]").click();'
            . 'const selected=check.checked&&!doc.querySelector("[data-filebank-confirm]").disabled;'
            . 'row.querySelector("[data-filebank-file-name]").click();'
            . 'return selected&&!check.checked&&!doc.querySelector("[data-filebank-save-review]")'
            . '&&row.querySelector("[data-filebank-rename]").classList.contains("filebank-pencil");})()'
        );
        if (!$correct) {
            throw new \Exception('Whole-row selection changed native import approval or exposed the old save control.');
        }
    }

    /**
     * Close the real Boost drawer before checking narrow-screen layout.
     *
     * @param int $width Width.
     */
    private function close_drawer(int $width): void {
        if ($width === 480) {
            $this->getSession()->getDriver()->executeScript(
                'window.filebankRowsReady=false;require(["theme_boost/drawers"],drawers=>'
                . '{drawers.closeAllDrawers();window.filebankRowsReady=true;});'
            );
            if (!$this->getSession()->wait(3000, 'window.filebankRowsReady&&!document.querySelector(".drawer.show")')) {
                throw new \Exception('The native drawer did not close before checking correspondence fields.');
            }
        }
    }
}
