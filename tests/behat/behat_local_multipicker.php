<?php
// This file is part of Moodle - http://moodle.org/
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
 * MultiPicker native material import integration.
 *
 * @package local_multipicker
 * @copyright 2026 MultiPicker contributors
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../../../lib/behat/behat_base.php');

/**
 * Exercises the native embedded chooser without replacing its transfer contract.
 */
class behat_local_multipicker extends behat_base {
    /**
     * Open the oldest retained configuration without assuming generated database IDs.
     * @When /^I compare the oldest MultiPicker profile version$/
     */
    public function compare_oldest_profile_version(): void {
        $this->getSession()->getDriver()->executeScript(
            'const links=[...document.querySelectorAll(".multipicker-row-actions a[href*=left]")];'
            . 'if(!links.length){throw new Error("Profile version comparison link is missing.");}'
            . 'links.at(-1).click();'
        );
    }

    /**
     * Check the real theme's clamping, card row alignment and centered navigation.
     * @Then /^MultiPicker cards and filters should keep a compact aligned layout$/
     */
    public function compact_browser_layout(): void {
        $this->wait_for_browser();
        $correct = $this->getSession()->getDriver()->evaluateScript(
            '(()=>{const doc=document.querySelector(".fp-object-container").contentDocument;'
            . 'const cards=[...doc.querySelectorAll(".multipicker-card")];'
            . 'const aligned=cards.every(card=>{const name=card.querySelector(".multipicker-card-title");'
            . 'return name.title&&doc.defaultView.getComputedStyle(name).webkitLineClamp==="2";});'
            . 'const button=doc.querySelector(".multipicker-open"),icon=button.querySelector(".icon");'
            . 'const b=button.getBoundingClientRect(),i=icon.getBoundingClientRect();'
            . 'const centered=Math.abs(b.x+b.width/2-i.x-i.width/2)<2'
            . '&&Math.abs(b.y+b.height/2-i.y-i.height/2)<2;'
            . 'const toggle=doc.querySelector("[data-multipicker-options]");toggle.open=true;'
            . 'const menu=toggle.querySelector(".multipicker-filter-menu");'
            . 'const bounded=menu.getBoundingClientRect().height<=353'
            . '&&doc.defaultView.getComputedStyle(menu).overflowY==="auto";'
            . 'toggle.open=false;return aligned&&centered&&bounded;})()'
        );
        if (!$correct) {
            throw new \Exception('Native card names, folder navigation or filter menu do not follow the compact layout.');
        }
    }

    /**
     * Exercise whole-row checks without disabling native transfer approval.
     * @Then /^the MultiPicker review should use compact selectable rows$/
     */
    public function compact_review_rows(): void {
        $this->wait_for_browser();
        $correct = $this->getSession()->getDriver()->evaluateScript(
            '(()=>{const doc=document.querySelector(".fp-object-container").contentDocument;'
            . 'const row=doc.querySelector(".multipicker-tree-file"),check=row.querySelector("input[type=checkbox]");'
            . 'row.querySelector("[data-multipicker-file-name]").click();'
            . 'const selected=check.checked&&!doc.querySelector("[data-multipicker-confirm]").disabled;'
            . 'row.querySelector("[data-multipicker-file-name]").click();'
            . 'return selected&&!check.checked&&!doc.querySelector("[data-multipicker-save-review]")'
            . '&&row.querySelector("[data-multipicker-rename]").classList.contains("multipicker-pencil");})()'
        );
        if (!$correct) {
            throw new \Exception('Whole-row selection changed native import approval or exposed the old save control.');
        }
    }

    /**
     * Verify actual visibility, icon-only content and accessible action labels.
     *
     * @Then /^MultiPicker profile bulk actions should be "(hidden|visible)"$/
     * @param string $state Expected visibility.
     */
    public function profile_bulk_actions(string $state): void {
        $visible = $state === 'visible' ? 'true' : 'false';
        $correct = $this->getSession()->getDriver()->evaluateScript(
            '(()=>{const bar=document.querySelector("[data-multipicker-bulk-actions]");'
            . 'const buttons=[...bar.querySelectorAll("button")];'
            . 'return (bar.getClientRects().length>0)===' . $visible . '&&buttons.length===4'
            . '&&buttons.every(button=>button.textContent.trim()===""&&button.getAttribute("title")'
            . '&&button.getAttribute("aria-label")&&button.querySelector(".icon"));})()'
        );
        if (!$correct) {
            throw new \Exception('Profile bulk actions do not match the selected state or native icon contract.');
        }
    }

    /**
     * Edit the actual row controls rather than posting an unrelated document.
     *
     * @When /^I set the MultiPicker destination folder rule to "([^"]*)"$/
     * @param string $name Exact target folder name.
     */
    public function destination_folder_rule(string $name): void {
        $this->getSession()->getDriver()->executeScript(
            'const method=document.querySelector("[data-field=targetmethod]");method.value="equals";'
            . 'method.dispatchEvent(new Event("input",{bubbles:true}));'
            . 'const value=document.querySelector("[data-field=targetvalue]");value.value=' . json_encode($name) . ';'
            . 'value.dispatchEvent(new Event("input",{bubbles:true}));'
        );
    }

    /**
     * Reconfiguration keeps the initial reviewed record intact.
     *
     * @Then /^MultiPicker should retain "([0-9]+)" migration plans$/
     * @param string $count Expected stored plans.
     */
    public function retained_migration_plans(string $count): void {
        global $DB;
        if ($DB->count_records('local_multipicker_plan') !== (int)$count) {
            throw new \Exception('Migration reconfiguration replaced its original plan.');
        }
    }

    /**
     * Exercise translated native schemas and profile submissions.
     *
     * @Given /^the MultiPicker administrator uses Spanish$/
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
     * @Then /^the MultiPicker profile inputs should align$/
     */
    public function profile_inputs_align(): void {
        $valid = $this->getSession()->getDriver()->evaluateScript(
            '(()=>{const names=["profilename","editmode","coursefield","coursepattern","familytemplate",'
            . '"displaytemplate","pairdimensions","categoryids"];'
            . 'const left=names.map(name=>document.getElementById("id_"+name).getBoundingClientRect().left);'
            . 'return Math.max(...left)-Math.min(...left)<=2;})()'
        );
        if (!$valid) {
            $positions = $this->getSession()->getDriver()->evaluateScript(
                'JSON.stringify(["profilename","editmode","coursefield","coursepattern","familytemplate",'
                . '"displaytemplate","pairdimensions","categoryids"].map(name=>{'
                . 'const input=document.getElementById("id_"+name);return {name,left:input.getBoundingClientRect().left,'
                . 'width:input.getBoundingClientRect().width,display:getComputedStyle(input).display};}))'
            );
            throw new \Exception('Profile inputs do not share the same native column: ' . $positions);
        }
    }

    /**
     * A native folder checkbox must update state without replacing result nodes.
     *
     * @When /^I select a MultiPicker card without rebuilding its results$/
     */
    public function select_without_rebuilding(): void {
        $this->wait_for_browser();
        $this->getSession()->getDriver()->executeScript(
            'const doc=document.querySelector(".fp-object-container").contentDocument;'
            . 'const results=doc.querySelector("[data-multipicker-results]");results.style.maxHeight="60px";'
            . 'results.scrollTop=results.scrollHeight;'
            . 'window.multipickerStableSelection={cards:[...results.querySelectorAll(".multipicker-card")],'
            . 'scroll:results.scrollTop,refresh:results.dataset.refresh||"0"};'
            . 'const choice=[...results.querySelectorAll("button[name=openfolder]")]'
            . '.find(button=>button.closest(".multipicker-card").textContent.includes("Published materials"))'
            . '.closest(".multipicker-card").querySelector("input");choice.click();'
        );
        if (
            !$this->getSession()->wait(
                15000,
                'document.querySelector(".fp-object-container").contentDocument.querySelector("[data-multipicker-results]")'
                . '.dataset.refresh!==window.multipickerStableSelection.refresh'
            )
        ) {
            throw new \Exception('The native selection request did not complete.');
        }
        $this->wait_for_browser();
        if (
            !$this->getSession()->getDriver()->evaluateScript(
                '(()=>{const doc=document.querySelector(".fp-object-container").contentDocument;'
                . 'const state=window.multipickerStableSelection;return state.cards.every(card=>card.isConnected)'
                . '&&doc.querySelector("[data-multipicker-results]").scrollTop===state.scroll;})()'
            )
        ) {
            throw new \Exception('Selecting a folder replaced result cards or changed the scroll position.');
        }
    }

    /**
     * Navigate to a real registered administrative entry point.
     *
     * @When /^I open MultiPicker administration "([^"]*)"$/
     * @param string $page Page name.
     */
    public function open_administration(string $page): void {
        if (!in_array($page, ['settings', 'profiles', 'migration', 'log'], true)) {
            throw new \Exception('Unknown administration page');
        }
        $this->getSession()->visit($this->locate_path('/local/multipicker/admin/' . $page . '.php'));
    }

    /**
     * Capture the actual native theme at desktop and mobile sizes.
     *
     * @Then /^I capture MultiPicker administration "([^"]*)"$/
     * @param string $name Screenshot basename.
     */
    public function capture_administration(string $name): void {
        foreach (['desktop' => 1366, 'mobile' => 480] as $size => $width) {
            $this->getSession()->resizeWindow($width, 1000);
            if ($size === 'mobile') {
                $this->getSession()->getDriver()->executeScript(
                    'window.multipickerCaptureReady=false;'
                    . 'require(["theme_boost/drawers"],drawers=>'
                    . '{drawers.closeAllDrawers();window.multipickerCaptureReady=true;});'
                );
                if (
                    !$this->getSession()->wait(
                        3000,
                        'window.multipickerCaptureReady&&!document.querySelector(".drawer.show,.modal-backdrop")'
                    )
                ) {
                    throw new \Exception('The native drawers did not close before the mobile capture.');
                }
            }
            $this->getSession()->getDriver()->executeScript('window.scrollTo(0,0);');
            file_put_contents('/tmp/multipicker-admin-' . $name . '-' . $size . '.png', $this->getSession()->getScreenshot());
        }
        $this->getSession()->resizeWindow(1366, 1000);
    }

    /**
     * Grant delegated profile management without granting site configuration.
     *
     * @Given /^the MultiPicker teacher can manage site profiles$/
     */
    public function grant_profile_management(): void {
        global $DB;
        $user = $DB->get_record('user', ['username' => 'cbteacher'], '*', MUST_EXIST);
        $roleid = create_role('Profile manager', 'profilemanager', '');
        assign_capability('local/multipicker:manageprofiles', CAP_ALLOW, $roleid, \context_system::instance()->id);
        role_assign($roleid, $user->id, \context_system::instance()->id);
        accesslib_clear_all_caches(true);
    }

    /**
     * Enter through the actual native Add files control and repository sidebar.
     *
     * @When /^I open the native MultiPicker$/
     * @When /^I open the native repository "([^"]*)"$/
     * @param string $name Native repository label.
     */
    public function open_native_picker(string $name = 'MultiPicker'): void {
        $this->getSession()->getDriver()->executeScript(
            'const add=document.querySelector(".filemanager .fp-btn-add a");'
            . 'if(!add){throw new Error("Native Add files action is unavailable");}add.click();'
        );
        $ready = $this->getSession()->wait(
            10000,
            '[...document.querySelectorAll(".fp-repo-name")].some(node=>node.textContent.trim()===' . json_encode($name) . ')'
        );
        if (!$ready) {
            throw new \Exception('The MultiPicker repository was not registered in the native picker.');
        }
        $this->getSession()->getDriver()->executeScript(
            '[...document.querySelectorAll(".fp-repo-name")].find(node=>node.textContent.trim()===' . json_encode($name) . ')'
            . '.closest("a").click();'
        );
        $this->wait_for_browser();
    }

    /**
     * Open Tiny's native link browser in an existing activity description.
     *
     * @When /^I open MultiPicker from the activity editor$/
     */
    public function open_editor_picker(): void {
        if (!$this->getSession()->wait(10000, '!!document.querySelector("button[data-mce-name=tiny_link_link]")')) {
            throw new \Exception('The native Tiny editor did not initialize.');
        }
        $this->getSession()->getDriver()->executeScript(
            'const button=document.querySelector("button[data-mce-name=tiny_link_link]");'
            . 'if(!button){throw new Error("Native Tiny Link toolbar action unavailable");}button.click();'
        );
        if (!$this->getSession()->wait(10000, '!!document.querySelector(".openlinkbrowser")')) {
            throw new \Exception('The native Tiny link browser did not open.');
        }
        $this->getSession()->getDriver()->executeScript('document.querySelector(".openlinkbrowser").click();');
        $this->getSession()->wait(
            10000,
            '[...document.querySelectorAll(".fp-repo-name")].some(node=>node.textContent.trim()==="MultiPicker")'
        );
        $this->getSession()->getDriver()->executeScript(
            '[...document.querySelectorAll(".fp-repo-name")].find(node=>node.textContent.trim()==="MultiPicker")'
            . '.closest("a").click();'
        );
        $this->wait_for_browser();
    }

    /**
     * Verify batch links in the actual editor before saving its native owning form.
     *
     * @Then /^the activity editor should contain MultiPicker links "([^\"]*)"$/
     * @param string $names Comma-separated filenames.
     */
    public function editor_has_links(string $names): void {
        $this->getSession()->getDriver()->executeScript(
            'window.multipickerEditor=null;require(["editor_tiny/editor"],module=>'
            . '{window.multipickerEditor=module.getInstanceForElementId("id_introeditor");});'
            . 'window.multipickerReceipt=null;fetch(M.cfg.wwwroot+"/local/multipicker/selection/status.php?"'
            . '+new URLSearchParams({scope:window.multipickerTestScope,sesskey:M.cfg.sesskey}))'
            . '.then(response=>response.json()).then(result=>{window.multipickerReceipt=result;});'
        );
        foreach (array_map('trim', explode(',', $names)) as $name) {
            if (
                !$this->getSession()->wait(
                    15000,
                    'window.multipickerEditor&&[...window.multipickerEditor.getBody().querySelectorAll("a[href]")]'
                    . '.some(link=>link.href.includes(' . json_encode($name) . '))'
                )
            ) {
                $receipt = $this->getSession()->getDriver()->evaluateScript(
                    'JSON.stringify({ready:window.multipickerReceipt?.ready,links:window.multipickerReceipt?.links,'
                    . 'files:window.multipickerReceipt?.files?.map(file=>file.name),'
                    . 'error:window.multipickerReceipt?.error,message:window.multipickerReceipt?.message})'
                );
                throw new \Exception('A reviewed file was not inserted into the native editor: ' . $name . '; ' . $receipt);
            }
        }
    }

    /**
     * Return from the review without changing the stored selection.
     *
     * @When /^I return to MultiPicker selection$/
     */
    public function back_to_selection(): void {
        $this->wait_for_browser();
        $this->getSession()->getDriver()->executeScript(
            'const doc=document.querySelector(".fp-object-container").contentDocument;'
            . 'window.multipickerPreviousDocument=doc;window.multipickerPreviousForm=doc.querySelector(".multi'
                . 'picker-browser");doc.querySelector(".multipicker-back").click();'
        );
        $this->getSession()->wait(
            10000,
            'document.querySelector(".fp-object-container")?.contentDocument?.querySelector(".multipicker-br'
                . 'owser")!==window.multipickerPreviousForm'
        );
        $this->wait_for_browser();
    }

    /**
     * Check that a real owning activity supplies the initial source type.
     *
     * @Then /^the MultiPicker module filter should be "([^\"]*)"$/
     * @param string $module Expected module value.
     */
    public function module_filter_is(string $module): void {
        $this->wait_for_browser();
        if (
            !$this->getSession()->getDriver()->evaluateScript(
                'document.querySelector(".fp-object-container").contentDocument.querySelector("select[name=modul'
                . 'e]").value==='
                . json_encode($module)
            )
        ) {
            throw new \Exception('The initial module filter did not match its owning activity.');
        }
    }

    /**
     * Change the rendered layout without navigating or losing checks.
     *
     * @When /^I switch MultiPicker layout to "(grid|list)"$/
     * @param string $layout Requested layout.
     */
    public function switch_layout(string $layout): void {
        $this->wait_for_browser();
        $this->getSession()->getDriver()->executeScript(
            'document.querySelector(".fp-object-container").contentDocument.querySelector('
            . json_encode('[data-multipicker-layout="' . $layout . '"]') . ').click();'
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
     * Select one visible source card by title and course.
     *
     * @When /^I select MultiPicker material "([^"]*)" from "([^"]*)"$/
     * @param string $title Visible file or folder title.
     * @param string $course Source shortname.
     */
    public function select_material(string $title, string $course): void {
        $this->wait_for_browser();
        $script = 'const doc=document.querySelector(".fp-object-container").contentDocument;'
            . 'const card=[...doc.querySelectorAll(".card-body")].find(card=>'
            . 'card.querySelector("label").textContent.trim()===' . json_encode($title)
            . '&&card.querySelector(".multipicker-origin").textContent.startsWith(' . json_encode($course) . '));'
            . 'if(!card){throw new Error("Source card unavailable");}'
            . 'card.querySelector(".multipicker-origin").click();'
            . 'if(!card.querySelector("input[type=checkbox]").checked){throw new Error("Whole-card selection failed");}';
        $this->getSession()->getDriver()->executeScript($script);
    }

    /**
     * Press an embedded action using the real HTML form or native chooser completion.
     *
     * @When /^I press MultiPicker action "([^"]*)"$/
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
            . 'if(doc.querySelector("form [name=scope]")){window.multipickerTestScope=doc.querySelector("form '
                . '[name=scope]").value;}'
            . 'window.multipickerPreviousDocument=doc;window.multipickerPreviousForm=doc.querySelector(".multi'
                . 'picker-browser");const button=[...doc.querySelectorAll("button")]'
            . '.find(button=>button.textContent.trim()==='
            . json_encode($label) . ');if(!button){throw new Error("Action unavailable");}button.click();'
        );
        if (in_array($label, ['Search materials', 'Review selection'], true)) {
            $this->getSession()->wait(
                10000,
                'document.querySelector(".fp-object-container")?.contentDocument?.querySelector(".multipicker-br'
                . 'owser")!==window.multipickerPreviousForm'
                . '&&document.querySelector(".fp-object-container")?.contentDocument?.readyState==="complete"'
            );
        }
        if ($label === 'Import reviewed materials') {
            $this->getSession()->wait(
                10000,
                '!document.querySelector(".file-picker") || document.querySelector(".file-picker").offsetParent===null'
            );
        }
    }

    /**
     * Check the actual rendered review, including native source declarations.
     *
     * @Then /^the MultiPicker review should include "([^"]*)"$/
     * @param string $text Expected review text.
     */
    public function review_includes(string $text): void {
        $this->wait_for_browser();
        $found = $this->getSession()->wait(
            10000,
            'document.querySelector(".fp-object-container")?.contentDocument?.querySelector(".multipicker-se'
                . 'lection-review")'
            . '&&(()=>{const doc=document.querySelector(".fp-object-container").contentDocument;'
            . 'return doc.querySelector(".multipicker-selection-review").textContent.includes(' . json_encode($text) . ')'
            . '||[...doc.querySelectorAll("input[name^=renames]")].some(input=>input.value.includes('
            . json_encode($text) . '));})()'
        );
        if (!$found) {
            throw new \Exception('Expected reviewed material was not rendered: ' . $text);
        }
    }

    /**
     * Set a visible browser filter and retain the native HTML form submission.
     *
     * @When /^I set MultiPicker filter "([^"]*)" to "([^"]*)"$/
     * @param string $name Filter field name.
     * @param string $value Requested filter value.
     */
    public function set_filter(string $name, string $value): void {
        $this->wait_for_browser();
        $this->getSession()->getDriver()->executeScript(
            'const doc=document.querySelector(".fp-object-container").contentDocument;'
            . 'window.multipickerPreviousDocument=doc;window.multipickerPreviousForm=doc.querySelector(".multi'
                . 'picker-browser");'
            . 'window.multipickerPreviousRefresh=doc.querySelector("[data-multipicker-results]").dataset.refresh;'
            . 'const field=doc.getElementsByName(' . json_encode($name) . ')[0];'
            . 'field.focus();field.value=' . json_encode($value) . ';field.dispatchEvent(new Event("input",{bubbles:true}));'
            . 'field.dispatchEvent(new Event("change",{bubbles:true}));'
        );
        if (
            !$this->getSession()->wait(
                10000,
                'document.querySelector(".fp-object-container")?.contentDocument?.querySelector("[data-multipick'
                . 'er-results]").dataset.refresh!==window.multipickerPreviousRefresh'
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
                . 'return doc===window.multipickerPreviousDocument&&doc.activeElement?.name==="q";})()'
            );
            if (!$retained) {
                throw new \Exception('Automatic search reloaded the document or lost the search field focus.');
            }
        }
    }

    /**
     * Count the visible file cards, independent of synthetic folder cards.
     *
     * @Then /^I should see "([0-9]+)" MultiPicker file cards named "([^"]*)"$/
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
     * @Then /^the MultiPicker browser should not include "([^"]*)"$/
     * @param string $text Excluded visible text.
     */
    public function browser_excludes(string $text): void {
        $this->wait_for_browser();
        $found = $this->getSession()->wait(
            10000,
            '(()=>{const doc=document.querySelector(".fp-object-container").contentDocument;'
            . 'return !doc.querySelector(".multipicker-browser").textContent.includes(' . json_encode($text) . ')'
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
     * @Given /^the MultiPicker teacher can only manage folder content$/
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
     * @When /^I remove MultiPicker reviewed file "([^"]*)"$/
     * @param string $filename Reviewed destination name.
     */
    public function remove_reviewed_file(string $filename): void {
        $this->press_action('Review selection');
        $this->wait_for_browser();
        $this->getSession()->getDriver()->executeScript(
            'const doc=document.querySelector(".fp-object-container").contentDocument;'
            . 'window.multipickerPreviousForm=doc.querySelector(".multipicker-browser");'
            . 'const input=[...doc.querySelectorAll("input[name^=renames]")].find(input=>input.value==='
            . json_encode($filename) . ');input.closest(".multipicker-tree-file").querySelector("button[name=removeitem]").click();'
        );
        $this->getSession()->wait(
            10000,
            'document.querySelector(".fp-object-container")?.contentDocument?.querySelector(".multipicker-br'
                . 'owser")!==window.multipickerPreviousForm'
        );
        $this->wait_for_browser();
        $this->back_to_selection();
    }


    /**
     * Navigate an actual source folder without changing its selection.
     *
     * @When /^I open MultiPicker folder "([^"]*)" from "([^"]*)"$/
     * @param string $name Folder label.
     * @param string $course Source shortname.
     */
    public function open_source_folder(string $name, string $course): void {
        $this->wait_for_browser();
        $this->getSession()->getDriver()->executeScript(
            'const doc=document.querySelector(".fp-object-container").contentDocument;'
            . 'window.multipickerPreviousForm=doc.querySelector(".multipicker-browser");'
            . 'const card=[...doc.querySelectorAll(".card-body")].find(card=>'
            . 'card.querySelector("label").textContent.trim()===' . json_encode($name)
            . '&&card.querySelector(".multipicker-origin")?.textContent.startsWith(' . json_encode($course) . '));'
            . 'card.querySelector(".multipicker-open").click();'
        );
        $this->getSession()->wait(
            10000,
            'document.querySelector(".fp-object-container")?.contentDocument?.querySelector(".multipicker-browser")'
            . '!==window.multipickerPreviousForm'
        );
        $this->wait_for_browser();
    }

    /**
     * Deselect one real file after recursively selecting its enclosing folder.
     *
     * @When /^I deselect MultiPicker file "([^"]*)"$/
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
     * @Then /^MultiPicker should have "([0-9]+)" selected files$/
     * @param int $count Expected resolved files.
     */
    public function selected_file_count(int $count): void {
        if (
            !$this->getSession()->wait(
                10000,
                'Number(document.querySelector(".fp-object-container")?.contentDocument'
                . '?.querySelector("[data-multipicker-count]")?.textContent)===' . $count
            )
        ) {
            throw new \Exception('The recursive file count is incorrect.');
        }
        $this->wait_for_browser();
    }

    /**
     * Rename through the visible review input and rebuild server approval.
     *
     * @When /^I rename MultiPicker reviewed file "([^"]*)" to "([^"]*)"$/
     * @param string $previous Current destination filename.
     * @param string $name New destination filename.
     */
    public function rename_reviewed_file(string $previous, string $name): void {
        $this->wait_for_browser();
        $this->getSession()->getDriver()->executeScript(
            'const doc=document.querySelector(".fp-object-container").contentDocument;'
            . 'window.multipickerPreviousForm=doc.querySelector(".multipicker-browser");'
            . 'const input=[...doc.querySelectorAll("input[name^=renames]")].find(input=>input.value==='
            . json_encode($previous) . ');input.closest(".multipicker-tree-file")'
            . '.querySelector("[data-multipicker-rename]").click();'
        );
        if (
            !$this->getSession()->wait(
                10000,
                '!!document.querySelector(".fp-object-container").contentDocument.querySelector("[data-multipick'
                . 'er-filename]")'
            )
        ) {
            throw new \Exception('The native rename modal did not open.');
        }
        $this->getSession()->getDriver()->executeScript(
            'const doc=document.querySelector(".fp-object-container").contentDocument;'
            . 'const input=doc.querySelector("[data-multipicker-filename]");input.value=' . json_encode($name) . ';'
            . 'input.closest(".modal").querySelector("[data-action=save]").click();'
        );
        $this->getSession()->wait(
            10000,
            'document.querySelector(".fp-object-container")?.contentDocument?.querySelector(".multipicker-browser")'
            . '!==window.multipickerPreviousForm'
        );
        $this->wait_for_browser();
    }


    /**
     * Use a real native instance name before rendering the owning activity.
     *
     * @Given /^the native MultiPicker repository is named "([^"]*)"$/
     * @param string $name Repository instance name.
     */
    public function name_repository(string $name): void {
        global $DB;
        $typeid = $DB->get_field('repository', 'id', ['type' => 'multipicker'], MUST_EXIST);
        $DB->set_field('repository_instances', 'name', $name, ['typeid' => $typeid]);
    }

    /**
     * Check the same configured name on the native sidebar and embedded heading.
     *
     * @Then /^the MultiPicker heading should be "([^"]*)"$/
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
     * @When /^I filter MultiPicker while its results are loading$/
     */
    public function filter_while_loading(): void {
        $this->wait_for_browser();
        $this->getSession()->getDriver()->executeScript(
            'const doc=document.querySelector(".fp-object-container").contentDocument;'
            . 'const frame=doc.defaultView;window.multipickerLiveFilter=null;'
            . 'const height=document.querySelector(".file-picker").getBoundingClientRect().height;'
            . 'frame.require(["core/fragment"],module=>{const fragment=module.default||module;'
            . 'const load=fragment.loadFragment.bind(fragment);fragment.loadFragment=(...args)=>'
            . 'new Promise((resolve,reject)=>setTimeout(()=>load(...args).then(resolve,reject),500));'
            . 'const input=doc.querySelector("input[name=q]");input.focus();input.value="notes";'
            . 'input.dispatchEvent(new Event("input",{bubbles:true}));'
            . 'setTimeout(()=>{window.multipickerLiveFilter={input,height,enabled:!input.disabled,'
            . 'focused:doc.activeElement===input,busy:doc.querySelector("form").getAttribute("aria-busy")==="true"};'
            . 'input.value="notes2025";input.dispatchEvent(new Event("input",{bubbles:true}));},650);});'
        );
        if (
            !$this->getSession()->wait(
                15000,
                'window.multipickerLiveFilter&&document.querySelector(".fp-object-container").contentDocument'
                . '.querySelector(".multipicker-card-title")?.textContent.trim()==="notes2025.txt"'
            )
        ) {
            throw new \Exception('The latest filter text was not applied after the pending response.');
        }
        $this->wait_for_browser();
        $valid = $this->getSession()->getDriver()->evaluateScript(
            '(()=>{const doc=document.querySelector(".fp-object-container").contentDocument;'
            . 'const state=window.multipickerLiveFilter;return state.enabled&&state.focused&&state.busy'
            . '&&doc.activeElement===state.input&&state.input.value==="notes2025"'
            . '&&Math.abs(document.querySelector(".file-picker").getBoundingClientRect().height-state.height)<=1;})()'
        );
        if (!$valid) {
            throw new \Exception('Filter input, focus or modal dimensions changed during the refresh.');
        }
    }

    /**
     * Capture the actual embedded picker at desktop and mobile widths.
     *
     * @Then /^I capture the MultiPicker browser$/
     */
    public function capture_browser(): void {
        $this->wait_for_browser();
        $page = $this->getSession()->getDriver()->evaluateScript(
            'document.querySelector(".fp-object-container").contentDocument'
            . '.querySelector(".multipicker-selection-review")?"review":"browser"'
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
            file_put_contents('/tmp/multipicker-' . $page . '-' . $size . '.png', $this->getSession()->getScreenshot());
        }
        $this->getSession()->resizeWindow(1366, 1000);
    }

    /**
     * Await the real embedded document after native AJAX listing and form navigation.
     */
    private function wait_for_browser(): void {
        $ready = $this->getSession()->wait(
            10000,
            'document.querySelector(".fp-object-container")?.contentDocument?.readyState==="complete"'
            . '&&document.querySelector(".fp-object-container").contentDocument.querySelector(".multipicker-br'
                . 'owser")?.dataset.multipickerReady==="true"'
            . '&&document.querySelector(".fp-object-container").contentDocument.querySelector(".multipicker-br'
                . 'owser")?.getAttribute("aria-busy")!=="true"'
        );
        if (!$ready) {
            throw new \Exception('The native MultiPicker browser did not load.');
        }
    }
}
