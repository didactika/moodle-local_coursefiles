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
class behat_local_filebank extends behat_local_filebank_base {
    /**
     * Upload generated test data through Moodle's actual upload repository.
     *
     * @When /^I upload a generated FileBank profile to "([^"\\]*)" filemanager$/
     * @param string $field Native filemanager label.
     */
    public function upload_profile_fixture(string $field): void {
        $generator = testing_util::get_data_generator()->get_plugin_generator('local_filebank');
        $directory = make_request_directory();
        $path = $directory . '/test-profile.json';
        file_put_contents($path, json_encode($generator->create_profile_config()));
        $this->execute('behat_repository_upload::i_upload_file_to_filemanager', [$path, $field]);
    }

    /**
     * Native repository changes restore the header and size before reopening the embedded chooser.
     * @Then /^FileBank presentation should follow the active native repository$/
     */
    public function native_repository_presentation(): void {
        $driver = $this->getSession()->getDriver();
        $driver->executeScript(
            'window.nativePresentationHost=document.querySelector(".fp-object-container").closest(".file-picker");'
        );
        $expanded = $driver->evaluateScript(
            'window.nativePresentationHeight=window.nativePresentationHost.querySelector(".fp-content")'
                . '.getBoundingClientRect().height'
        );
        for ($cycle = 0; $cycle < 2; $cycle++) {
            $driver->executeScript(
                '[...document.querySelectorAll(".fp-repo-name")].find(node=>node.textContent.trim()==="Server files")'
                    . '.closest("a").click();'
            );
            if (
                !$this->getSession()->wait(
                    10000,
                    'window.nativePresentationHost.querySelector(".fp-repo.active .fp-repo-name")'
                        . '?.textContent.trim()==="Server files"'
                        . '&&getComputedStyle(window.nativePresentationHost.querySelector(".fp-navbar")).display!=="none"'
                        . '&&!window.nativePresentationHost.querySelector(".fp-object-container")'
                        . '&&!document.querySelector(".filebank-native-host,.multipicker-native-host")'
                )
            ) {
                $state = $driver->evaluateScript(
                    'JSON.stringify({host:window.nativePresentationHost.className,'
                        . 'navbar:getComputedStyle(window.nativePresentationHost.querySelector(".fp-navbar")).display,'
                        . 'objects:window.nativePresentationHost.querySelectorAll(".fp-object-container").length})'
                );
                throw new Exception('Switching repository did not restore the native header: ' . $state);
            }
            $height = $driver->evaluateScript(
                'window.nativePresentationHost.querySelector(".fp-content").getBoundingClientRect().height'
            );
            if (abs($height - $expanded) < 1 || ($cycle && abs($height - $normal) > 1)) {
                throw new Exception(
                    'Switching repository did not restore a stable native content height: '
                        . json_encode(['expanded' => $expanded, 'native' => $height, 'previous' => $normal ?? null])
                );
            }
            $normal = $height;
            $driver->executeScript(
                '[...document.querySelectorAll(".fp-repo-name")].find(node=>node.textContent.trim()==='
                    . json_encode('Course library') . ').closest("a").click();'
            );
            if (
                !$this->getSession()->wait(
                    10000,
                    '!!document.querySelector(".fp-object-container")?.contentDocument?.querySelector(".filebank-browser")'
                        . '&&getComputedStyle(window.nativePresentationHost.querySelector(".fp-navbar")).display==="none"'
                )
            ) {
                throw new Exception('Reopening the repository did not restore the embedded chooser presentation.');
            }
            $height = $driver->evaluateScript(
                'window.nativePresentationHost.querySelector(".fp-content").getBoundingClientRect().height'
            );
            if (abs($height - $expanded) > 1) {
                throw new Exception('Reopening the repository changed its expanded height.');
            }
        }
    }

    /**
     * Browser-to-review navigation keeps the same native panel presentation.
     * @Then /^FileBank should retain its expanded review panel$/
     */
    public function native_review_presentation(): void {
        $valid = $this->getSession()->getDriver()->evaluateScript(
            'getComputedStyle(window.nativePresentationHost.querySelector(".fp-navbar")).display==="none"'
                . '&&Math.abs(window.nativePresentationHost.querySelector(".fp-content").getBoundingClientRect().height'
                . '-window.nativePresentationHeight)<1'
        );
        if (!$valid) {
            throw new Exception('Review navigation changed the embedded panel height or exposed the native toolbar.');
        }
    }

    /**
     * Retain actual DOM nodes to detect navigation or lost native checkbox properties.
     * @When /^I remember the selected FileBank profile rows$/
     */
    public function remember_profile_rows(): void {
        $this->getSession()->getDriver()->executeScript(
            'window.filebankProfileRows=[...document.querySelectorAll("input[name=\\"profiles[]\\"]")]'
            . '.filter(check=>check.checked);'
        );
    }

    /**
     * State actions must retain checked rows rather than reloading the list.
     * @Then /^FileBank should retain its selected profile rows$/
     */
    public function retain_profile_rows(): void {
        $correct = $this->getSession()->getDriver()->evaluateScript(
            '!!window.filebankProfileRows?.length'
            . '&&window.filebankProfileRows.every(check=>check.isConnected&&check.checked)'
        );
        if (!$correct) {
            throw new \Exception('A state change replaced the profile list or cleared its selection.');
        }
    }

    /**
     * Exercise native help links with actual pointer clicks inside the picker iframe.
     * @Then /^all FileBank filter help icons should open their content$/
     */
    public function filter_help_opens(): void {
        $this->wait_for_browser();
        $this->getSession()->getDriver()->executeScript(
            'const frame=document.querySelector(".fp-object-container");frame.id="filebankhelpframe";'
            . 'frame.name="filebankhelpframe";'
            . 'frame.contentDocument.querySelector("[data-filebank-options]").open=true;'
        );
        $this->getSession()->switchToIFrame('filebankhelpframe');
        try {
            $selector = '.filebank-browser-filters a[data-toggle="popover"],'
                . '.filebank-browser-filters a[data-bs-toggle="popover"]';
            $links = $this->getSession()->getPage()->findAll('css', $selector);
            if (count($links) < 6) {
                throw new \Exception('Specific filter and copy-option help links are missing.');
            }
            foreach ($links as $link) {
                $this->getSession()->getDriver()->executeScript(
                    'document.querySelector("[data-filebank-options]").open=true;'
                );
                $link->click();
                if (
                    !$this->getSession()->wait(
                        3000,
                        '!!document.querySelector(".popover.show .popover-body")'
                        . '&&document.querySelector(".popover.show .popover-body").textContent.trim().length>10'
                        . '&&document.activeElement.tagName!=="INPUT"'
                    )
                ) {
                    throw new \Exception('A native filter help link did not open readable content.');
                }
                $this->getSession()->getDriver()->executeScript('document.activeElement.blur();');
            }
        } finally {
            $this->getSession()->switchToIFrame(null);
            $this->getSession()->getDriver()->executeScript(
                'document.querySelector(".fp-object-container").contentDocument'
                . '.querySelector("[data-filebank-options]").open=false;'
            );
        }
    }
}
