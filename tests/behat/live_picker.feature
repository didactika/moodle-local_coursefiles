@local @local_multipicker @javascript
Feature: Stable native selection with the configured repository name
  As a teacher
  I need continuous filtering and a recognizable material library

  Background:
    Given the following "local_multipicker > folder fixtures" exist:
      | name           |
      | Live selection |
    And the native MultiPicker repository is named "Course library"
    And I am on the "Target materials" "folder activity" page logged in as "cbteacher"
    And I press "Edit"
    And I open the native repository "Course library"

  Scenario: Configured name and whole-result selection use the native picker
    Then the MultiPicker heading should be "Course library"
    And MultiPicker cards and filters should keep a compact aligned layout
    And MultiPicker layouts should share selections and mark the active view
    When I press MultiPicker action "Select all"
    Then MultiPicker should have "3" selected files
    When I press MultiPicker action "Clear selection"
    Then MultiPicker should have "0" selected files

  Scenario: Typing remains active and the modal does not resize while results reload
    When I filter MultiPicker while its results are loading
    Then I should see "1" MultiPicker file cards named "notes2025.txt"
    And I capture the MultiPicker browser

  Scenario: Folder selection preserves cards and the current results scroll
    When I select a MultiPicker card without rebuilding its results
    Then MultiPicker should have "2" selected files

  @multipicker_views
  Scenario: Cards list and lazy tree share checked content without flat course headings
    Then MultiPicker selections should stay consistent across all views

  @multipicker_views
  Scenario: Clearing a pending search retains the active view and selected material
    Then MultiPicker should clear pending filters without losing its view or selection

  Scenario: Review rows select files and retain native import approval
    When I press MultiPicker action "Select all"
    And I press MultiPicker action "Review selection"
    Then the MultiPicker review should use compact selectable rows
    And MultiPicker review folders should select their files and reveal removal
    And I capture the MultiPicker browser

  Scenario: Native filter help opens without focusing the input and All is not counted
    Then all MultiPicker filter help icons should open their content
    And MultiPicker should show "1" active filters
    When I set MultiPicker filter "module" to ""
    Then MultiPicker should show "0" active filters
    When I set MultiPicker filter "q" to "notes"
    Then MultiPicker should show "1" active filters

  Scenario: Collapsed tree folders load nested selectable files without navigating away
    Then MultiPicker tree folders should load through to selectable files

  Scenario: Returning from a folder after switching to tree preserves navigation
    Then MultiPicker Back should work after switching layouts inside a folder

  Scenario: Already imported content stays hidden after renaming and reopening the native picker
    When I select MultiPicker material "Published materials" from "CB2025"
    And I press MultiPicker action "Review selection"
    And I rename MultiPicker reviewed file "lesson.txt" to "already-used.txt"
    And I press MultiPicker action "Import materials"
    And I press "Save changes"
    And I press "Edit"
    And I open the native repository "Course library"
    Then the MultiPicker browser should not include "CB2025"
    When I set MultiPicker filter "q" to "lesson"
    Then I should see "0" MultiPicker file cards named "lesson.txt"
    When I set MultiPicker filter "q" to "notes2025"
    Then I should see "0" MultiPicker file cards named "notes2025.txt"
