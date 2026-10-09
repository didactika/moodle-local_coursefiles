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
 * CourseBridge native material import integration.
 *
 * @package local_coursebridge
 * @copyright 2026 CourseBridge contributors
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../../../lib/behat/behat_base.php');

/**
 * Exercises the native embedded chooser without replacing its transfer contract.
 */
class behat_local_coursebridge extends behat_base {
    /**
     * Navigate to a real registered administrative entry point.
     *
     * @When /^I open CourseBridge administration "([^"]*)"$/
     * @param string $page Page name.
     */
    public function open_administration(string $page): void {
        if (!in_array($page, ['settings', 'profiles', 'migration', 'log'], true)) {
            throw new \Exception('Unknown administration page');
        }
        $this->getSession()->visit($this->locate_path('/local/coursebridge/admin/' . $page . '.php'));
    }

    /**
     * Capture the actual native theme at desktop and mobile sizes.
     *
     * @Then /^I capture CourseBridge administration "([^"]*)"$/
     * @param string $name Screenshot basename.
     */
    public function capture_administration(string $name): void {
        foreach (['desktop' => 1366, 'mobile' => 480] as $size => $width) {
            $this->getSession()->resizeWindow($width, 1000);
            if ($size === 'mobile') {
                $this->getSession()->getDriver()->executeScript(
                    'window.coursebridgeCaptureReady=false;'
                    . 'require(["theme_boost/drawers"],drawers=>'
                    . '{drawers.closeAllDrawers();window.coursebridgeCaptureReady=true;});'
                );
                if (
                    !$this->getSession()->wait(
                        3000,
                        'window.coursebridgeCaptureReady&&!document.querySelector(".drawer.show,.modal-backdrop")'
                    )
                ) {
                    throw new \Exception('The native drawers did not close before the mobile capture.');
                }
            }
            $this->getSession()->getDriver()->executeScript('window.scrollTo(0,0);');
            file_put_contents('/tmp/coursebridge-admin-' . $name . '-' . $size . '.png', $this->getSession()->getScreenshot());
        }
        $this->getSession()->resizeWindow(1366, 1000);
    }

    /**
     * Grant delegated profile management without granting site configuration.
     *
     * @Given /^the CourseBridge teacher can manage site profiles$/
     */
    public function grant_profile_management(): void {
        global $DB;
        $user = $DB->get_record('user', ['username' => 'cbteacher'], '*', MUST_EXIST);
        $roleid = create_role('Profile manager', 'profilemanager', '');
        assign_capability('local/coursebridge:manageprofiles', CAP_ALLOW, $roleid, \context_system::instance()->id);
        role_assign($roleid, $user->id, \context_system::instance()->id);
        accesslib_clear_all_caches(true);
    }

    /**
     * Select one visible source card by title and course.
     *
     * @When /^I select CourseBridge material "([^"]*)" from "([^"]*)"$/
     * @param string $title Visible file or folder title.
     * @param string $course Source shortname.
     */
    public function select_material(string $title, string $course): void {
        $this->wait_for_browser();
        $script = 'const doc=document.querySelector(".fp-object-container").contentDocument;'
            . 'const card=[...doc.querySelectorAll(".card-body")].find(card=>'
            . 'card.querySelector("label").textContent.trim()===' . json_encode($title)
            . '&&card.querySelector(".small").textContent.startsWith(' . json_encode($course) . '));'
            . 'if(!card){throw new Error("Source card unavailable");}'
            . 'const input=card.querySelector("input[type=checkbox]");input.checked=true;'
            . 'input.dispatchEvent(new Event("change",{bubbles:true}));';
        $this->getSession()->getDriver()->executeScript($script);
    }

    /**
     * Press an embedded action using the real HTML form or native chooser completion.
     *
     * @When /^I press CourseBridge action "([^"]*)"$/
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
            . 'window.coursebridgePreviousDocument=doc;const button=[...doc.querySelectorAll("button")]'
            . '.find(button=>button.textContent.trim()==='
            . json_encode($label) . ');if(!button){throw new Error("Action unavailable");}button.click();'
        );
        if (in_array($label, ['Search materials', 'Review selection'], true)) {
            $this->getSession()->wait(
                10000,
                'document.querySelector(".fp-object-container")?.contentDocument!==window.coursebridgePreviousDocument'
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
     * @Then /^the CourseBridge review should include "([^"]*)"$/
     * @param string $text Expected review text.
     */
    public function review_includes(string $text): void {
        $this->wait_for_browser();
        $found = $this->getSession()->wait(
            10000,
            'document.querySelector(".fp-object-container")?.contentDocument?.querySelector(".coursebridge-selection-review")'
            . '?.textContent.includes(' . json_encode($text) . ')'
        );
        if (!$found) {
            throw new \Exception('Expected reviewed material was not rendered: ' . $text);
        }
    }

    /**
     * Set a visible browser filter and retain the native HTML form submission.
     *
     * @When /^I set CourseBridge filter "([^"]*)" to "([^"]*)"$/
     * @param string $name Filter field name.
     * @param string $value Requested filter value.
     */
    public function set_filter(string $name, string $value): void {
        $this->wait_for_browser();
        $this->getSession()->getDriver()->executeScript(
            'const doc=document.querySelector(".fp-object-container").contentDocument;'
            . 'const field=doc.querySelector("[name="+' . json_encode($name) . '+"]");'
            . 'field.value=' . json_encode($value) . ';field.dispatchEvent(new Event("input",{bubbles:true}));'
        );
    }

    /**
     * Count the visible file cards, independent of synthetic folder cards.
     *
     * @Then /^I should see "([0-9]+)" CourseBridge file cards named "([^"]*)"$/
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
     * @Then /^the CourseBridge browser should not include "([^"]*)"$/
     * @param string $text Excluded visible text.
     */
    public function browser_excludes(string $text): void {
        $this->wait_for_browser();
        $found = $this->getSession()->wait(
            10000,
            '!document.querySelector(".fp-object-container").contentDocument.querySelector(".coursebridge-browser")'
            . '.textContent.includes(' . json_encode($text) . ')'
        );
        if (!$found) {
            throw new \Exception('An unavailable or filtered origin was shown: ' . $text);
        }
    }

    /**
     * Deny activity settings while retaining the native Folder content permission.
     *
     * @Given /^the CourseBridge teacher can only manage folder content$/
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
     * Await the real embedded document after native AJAX listing and form navigation.
     */
    private function wait_for_browser(): void {
        $ready = $this->getSession()->wait(
            10000,
            'document.querySelector(".fp-object-container")?.contentDocument?.readyState==="complete"'
            . '&&!!document.querySelector(".fp-object-container").contentDocument.querySelector(".coursebridge-browser")'
        );
        if (!$ready) {
            throw new \Exception('The native CourseBridge browser did not load.');
        }
    }
}
