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
class behat_local_filebank_steps_4 extends behat_local_filebank_base {
    /**
     * Navigate to a real registered administrative entry point.
     *
     * @When /^I open FileBank administration "([^"]*)"$/
     * @param string $page Page name.
     */
    public function open_administration(string $page): void {
        if ($page === 'category') {
            $this->getSession()->visit($this->locate_path('/admin/category.php?category=local_filebank'));
            return;
        }
        if (!in_array($page, ['settings', 'profiles', 'migration', 'log'], true)) {
            throw new \Exception('Unknown administration page');
        }
        $this->getSession()->visit($this->locate_path('/local/filebank/admin/' . $page . '.php'));
    }

    /**
     * Switch shared nodes between native grid, list and a collapsible course tree.
     * @Then /^FileBank layouts should share selections and mark the active view$/
     */
    public function native_browser_layouts(): void {
        global $CFG;
        $this->wait_for_browser();
        if (
            $CFG->version >= 2026100500 && !$this->getSession()->wait(
                10000,
                '!!document.querySelector(".fp-object-container")?.contentDocument'
                . '?.querySelector("[data-filebank-react-view]")'
            )
        ) {
            throw new \Exception('The native Moodle 5.3 React layout component did not mount.');
        }
        $correct = $this->getSession()->getDriver()->evaluateScript(
            '(()=>{const doc=document.querySelector(".fp-object-container").contentDocument;'
            . 'const form=doc.querySelector(".filebank-browser"),card=doc.querySelector(".filebank-card");'
            . 'for(const layout of ["list","tree"]){form.querySelector("[data-filebank-layout="+layout+"]").click();'
            . 'if(form.dataset.layout!==layout||form.querySelectorAll("[data-filebank-layout].active").length!==1)'
            . '{return false;}}const group=doc.querySelector(".filebank-browser-tree-group");'
            . 'if(!group.querySelector("[data-filebank-course-toggle]").getClientRects().length){return false;}'
            . 'group.querySelector("[data-filebank-course-toggle]").click();'
            . 'const hidden=!group.querySelector(".filebank-card").getClientRects().length;'
            . 'form.querySelector("[data-filebank-layout=grid]").click();'
            . 'return hidden&&group.dataset.collapsed==="false"&&card===doc.querySelector(".filebank-card")'
            . '&&doc.querySelector("[data-filebank-layout=grid]").getAttribute("aria-pressed")==="true"'
            . '&&[...doc.querySelectorAll("[data-filebank-course-toggle]")].every(heading=>'
            . '!heading.getClientRects().length);})()'
        );
        if (!$correct) {
            throw new \Exception('The course tree, native active view or shared result nodes are incorrect.');
        }
        $this->getSession()->getDriver()->executeScript(
            'document.querySelector(".fp-object-container").contentDocument'
            . '.querySelector("[data-filebank-layout=tree]").click();'
            . 'window.filebankTreePainted=false;requestAnimationFrame(()=>requestAnimationFrame(()=>'
            . '{window.filebankTreePainted=true;}));'
        );
        if (!$this->getSession()->wait(3000, 'window.filebankTreePainted')) {
            throw new \Exception('The native tree layout did not finish painting.');
        }
        file_put_contents('/tmp/filebank-tree-desktop.png', $this->getSession()->getScreenshot());
        $this->getSession()->getDriver()->executeScript(
            'document.querySelector(".fp-object-container").contentDocument'
            . '.querySelector("[data-filebank-layout=grid]").click();'
        );
    }

    /**
     * Folder removal checks include descendants and share the import action row.
     * @Then /^FileBank review folders should select their files and reveal removal$/
     */
    public function review_folder_removal(): void {
        $this->wait_for_browser();
        $correct = $this->getSession()->getDriver()->evaluateScript(
            '(()=>{const doc=document.querySelector(".fp-object-container").contentDocument;'
            . 'const remove=doc.querySelector("[data-filebank-remove-selected]");if(!remove.hidden){return false;}'
            . 'const folder=doc.querySelector("[data-filebank-review-folder]");if(!folder){return false;}folder.click();'
            . 'const files=[...folder.closest("details").querySelectorAll("input[name=\\"removefiles[]\\"]")];'
            . 'const selected=files.every(check=>check.checked)&&!remove.hidden&&!remove.disabled;'
            . 'const importButton=doc.querySelector("[data-filebank-confirm]");'
            . 'const aligned=Math.abs(remove.getBoundingClientRect().y-importButton.getBoundingClientRect().y)<2;'
            . 'const approved=!importButton.disabled;'
            . 'folder.click();return selected&&aligned&&approved&&!importButton.disabled'
            . '&&files.every(check=>!check.checked)&&remove.hidden;})()'
        );
        if (!$correct) {
            throw new \Exception('Review folder selection or the conditional footer removal action is incorrect.');
        }
    }

    /**
     * Load real recursive fragments, select their owning folder and retain all expanded nodes.
     * @Then /^FileBank tree folders should load through to selectable files$/
     */
    public function native_tree_files(): void {
        $this->wait_for_browser();
        $this->getSession()->getDriver()->executeScript(
            '(()=>{const doc=document.querySelector(".fp-object-container").contentDocument;'
            . 'doc.querySelector("[data-filebank-layout=tree]").click();'
            . 'const root=doc.querySelector("[data-tree-folder]");'
            . 'window.filebankTreeTest={root,entries:root.querySelector("[data-tree-entries]")};'
            . 'if(!window.filebankTreeTest.entries.hidden||window.filebankTreeTest.entries.children.length)'
            . '{throw new Error("Tree children were eagerly loaded");}'
            . 'root.querySelector("[name=openfolder]").click();})()'
        );
        if (!$this->getSession()->wait(10000, 'window.filebankTreeTest.entries.dataset.loaded==="true"')) {
            throw new \Exception('The first native tree branch did not load.');
        }
        $this->getSession()->getDriver()->executeScript(
            '(()=>{const state=window.filebankTreeTest;const child=state.entries.querySelector("[data-tree-folder]");'
            . 'if(!child||!child.querySelector("[data-tree-entries]").hidden'
            . '||!state.entries.querySelector("[data-filebank-choice][value^=\\"file:\\"]"))'
            . '{throw new Error("Missing selectable files or collapsed nested folder");}'
            . 'state.child=child;state.nested=child.querySelector("[data-tree-entries]");'
            . 'child.querySelector("[name=openfolder]").click();})()'
        );
        if (!$this->getSession()->wait(10000, 'window.filebankTreeTest.nested.dataset.loaded==="true"')) {
            throw new \Exception('The nested native tree branch did not load.');
        }
        $this->getSession()->getDriver()->executeScript(
            'window.filebankTreeTest.root.querySelector("[data-filebank-choice]").click();'
        );
        $this->execute('behat_local_filebank_steps_8::selected_file_count', [2]);
        $correct = $this->getSession()->getDriver()->evaluateScript(
            '(()=>{const state=window.filebankTreeTest;'
            . 'const checks=[...state.root.querySelectorAll("[data-filebank-choice]")];'
            . 'return checks.length>=4&&checks.every(check=>check.checked)&&state.child.isConnected'
            . '&&state.nested.querySelector("[data-filebank-choice][value^=\\"file:\\"]")!==null;})()'
        );
        if (!$correct) {
            throw new \Exception('Recursive tree selection did not update files and nested folder checks in place.');
        }
        file_put_contents('/tmp/filebank-recursive-tree.png', $this->getSession()->getScreenshot());
    }

    /**
     * The tree disclosure handler must not intercept native parent navigation.
     * @Then /^FileBank Back should work after switching layouts inside a folder$/
     */
    public function native_tree_back(): void {
        $this->wait_for_browser();
        $this->getSession()->getDriver()->executeScript(
            '(()=>{const doc=document.querySelector(".fp-object-container").contentDocument;'
            . 'doc.querySelector(".filebank-open").click();})()'
        );
        if (
            !$this->getSession()->wait(
                10000,
                '!!document.querySelector(".fp-object-container")?.contentDocument?.querySelector(".filebank-back")'
            )
        ) {
            throw new \Exception('The folder did not open.');
        }
        $this->wait_for_browser();
        $this->getSession()->getDriver()->executeScript(
            '(()=>{const doc=document.querySelector(".fp-object-container").contentDocument;'
            . 'doc.querySelector("[data-filebank-layout=tree]").click();'
            . 'doc.querySelector(".filebank-back").click();})()'
        );
        if (
            !$this->getSession()->wait(
                10000,
                'document.querySelector(".fp-object-container")?.contentDocument?.readyState==="complete"'
                . '&&!document.querySelector(".fp-object-container")?.contentDocument?.querySelector(".filebank-back")'
            )
        ) {
            throw new \Exception('Tree layout intercepted native Back navigation.');
        }
        $this->wait_for_browser();
    }

    /**
     * Capture the actual native theme at desktop and mobile sizes.
     *
     * @Then /^I capture FileBank administration "([^"]*)"$/
     * @param string $name Screenshot basename.
     */
    public function capture_administration(string $name): void {
        foreach (['desktop' => 1366, 'mobile' => 480] as $size => $width) {
            $this->getSession()->resizeWindow($width, 1000);
            if ($size === 'mobile') {
                $this->getSession()->getDriver()->executeScript(
                    'window.filebankCaptureReady=false;'
                    . 'require(["theme_boost/drawers"],drawers=>'
                    . '{drawers.closeAllDrawers();window.filebankCaptureReady=true;});'
                );
                if (
                    !$this->getSession()->wait(
                        3000,
                        'window.filebankCaptureReady&&!document.querySelector(".drawer.show")'
                    )
                ) {
                    $left = $this->getSession()->getDriver()->evaluateScript(
                        'return String(window.filebankCaptureReady)+"|"+Array.from(document.querySelectorAll('
                        . '".drawer.show,.modal-backdrop.show")).map(e=>e.className).join(",");'
                    );
                    throw new \Exception('The native drawers did not close before the mobile capture: ' . $left);
                }
            }
            $this->getSession()->getDriver()->executeScript('window.scrollTo(0,0);');
            file_put_contents('/tmp/filebank-admin-' . $name . '-' . $size . '.png', $this->getSession()->getScreenshot());
        }
        $this->getSession()->resizeWindow(1366, 1000);
    }
}
