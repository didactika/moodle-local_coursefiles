@local @local_filebank @javascript
Feature: Stable native selection with the configured repository name
  As a teacher
  I need continuous filtering and a recognizable material library

  Background:
    Given the following "local_filebank > folder fixtures" exist:
      | name           |
      | Live selection |
    And the native FileBank repository is named "Course library"
    And I am on the "Target materials" "folder activity" page logged in as "cbteacher"
    And I press "Edit"
    And I open the native repository "Course library"

  Scenario: Configured name and whole-result selection use the native picker
    Then the FileBank heading should be "Course library"
    And FileBank cards and filters should keep a compact aligned layout
    And FileBank layouts should share selections and mark the active view
    When I press FileBank action "Select all"
    Then FileBank should have "3" selected files
    When I press FileBank action "Clear selection"
    Then FileBank should have "0" selected files

  @picker_presentation
  Scenario: Switching repositories restores the native header and content size
    Then FileBank presentation should follow the active native repository
    When I press FileBank action "Select all"
    And I press FileBank action "Review selection"
    Then the FileBank review should use compact selectable rows
    And FileBank should retain its expanded review panel

  Scenario: Typing remains active and the modal does not resize while results reload
    When I filter FileBank while its results are loading
    Then I should see "1" FileBank file cards named "notes2025.txt"
    And I capture the FileBank browser

  Scenario: Folder selection preserves cards and the current results scroll
    When I select a FileBank card without rebuilding its results
    Then FileBank should have "2" selected files

  @filebank_views
  Scenario: Cards list and lazy tree share checked content without flat course headings
    Then FileBank selections should stay consistent across all views

  @filebank_views
  Scenario: Clearing a pending search retains the active view and selected material
    Then FileBank should clear pending filters without losing its view or selection

  Scenario: Review rows select files and retain native import approval
    When I press FileBank action "Select all"
    And I press FileBank action "Review selection"
    Then the FileBank review should use compact selectable rows
    And FileBank review folders should select their files and reveal removal
    And I capture the FileBank browser

  Scenario: Native filter help opens without focusing the input and All is not counted
    Then all FileBank filter help icons should open their content
    And FileBank should show "1" active filters
    When I set FileBank filter "module" to ""
    Then FileBank should show "0" active filters
    When I set FileBank filter "q" to "notes"
    Then FileBank should show "1" active filters

  Scenario: Collapsed tree folders load nested selectable files without navigating away
    Then FileBank tree folders should load through to selectable files

  Scenario: Returning from a folder after switching to tree preserves navigation
    Then FileBank Back should work after switching layouts inside a folder

  Scenario: Already imported content stays hidden after renaming and reopening the native picker
    When I select FileBank material "Published materials" from "CB2025"
    And I press FileBank action "Review selection"
    And I rename FileBank reviewed file "lesson.txt" to "already-used.txt"
    And I press FileBank action "Import materials"
    And I press "Save changes"
    And I press "Edit"
    And I open the native repository "Course library"
    Then the FileBank browser should not include "CB2025"
    When I set FileBank filter "q" to "lesson"
    Then I should see "0" FileBank file cards named "lesson.txt"
    When I set FileBank filter "q" to "notes2025"
    Then I should see "0" FileBank file cards named "notes2025.txt"
