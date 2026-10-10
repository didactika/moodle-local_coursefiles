@local @local_coursefiles @javascript
Feature: Stable native selection with the configured repository name
  As a teacher
  I need continuous filtering and a recognizable material library

  Background:
    Given the following "local_coursefiles > folder fixtures" exist:
      | name           |
      | Live selection |
    And the native CourseFiles repository is named "Course library"
    And I am on the "Target materials" "folder activity" page logged in as "cbteacher"
    And I press "Edit"
    And I open the native repository "Course library"

  Scenario: Configured name and whole-result selection use the native picker
    Then the CourseFiles heading should be "Course library"
    And CourseFiles cards and filters should keep a compact aligned layout
    And CourseFiles layouts should share selections and mark the active view
    When I press CourseFiles action "Select all"
    Then CourseFiles should have "3" selected files
    When I press CourseFiles action "Clear selection"
    Then CourseFiles should have "0" selected files

  Scenario: Typing remains active and the modal does not resize while results reload
    When I filter CourseFiles while its results are loading
    Then I should see "1" CourseFiles file cards named "notes2025.txt"
    And I capture the CourseFiles browser

  Scenario: Folder selection preserves cards and the current results scroll
    When I select a CourseFiles card without rebuilding its results
    Then CourseFiles should have "2" selected files

  @coursefiles_views
  Scenario: Cards list and lazy tree share checked content without flat course headings
    Then CourseFiles selections should stay consistent across all views

  @coursefiles_views
  Scenario: Clearing a pending search retains the active view and selected material
    Then CourseFiles should clear pending filters without losing its view or selection

  Scenario: Review rows select files and retain native import approval
    When I press CourseFiles action "Select all"
    And I press CourseFiles action "Review selection"
    Then the CourseFiles review should use compact selectable rows
    And CourseFiles review folders should select their files and reveal removal
    And I capture the CourseFiles browser

  Scenario: Native filter help opens without focusing the input and All is not counted
    Then all CourseFiles filter help icons should open their content
    And CourseFiles should show "1" active filters
    When I set CourseFiles filter "module" to ""
    Then CourseFiles should show "0" active filters
    When I set CourseFiles filter "q" to "notes"
    Then CourseFiles should show "1" active filters

  Scenario: Collapsed tree folders load nested selectable files without navigating away
    Then CourseFiles tree folders should load through to selectable files

  Scenario: Returning from a folder after switching to tree preserves navigation
    Then CourseFiles Back should work after switching layouts inside a folder

  Scenario: Already imported content stays hidden after renaming and reopening the native picker
    When I select CourseFiles material "Published materials" from "CB2025"
    And I press CourseFiles action "Review selection"
    And I rename CourseFiles reviewed file "lesson.txt" to "already-used.txt"
    And I press CourseFiles action "Import materials"
    And I press "Save changes"
    And I press "Edit"
    And I open the native repository "Course library"
    Then the CourseFiles browser should not include "CB2025"
    When I set CourseFiles filter "q" to "lesson"
    Then I should see "0" CourseFiles file cards named "lesson.txt"
    When I set CourseFiles filter "q" to "notes2025"
    Then I should see "0" CourseFiles file cards named "notes2025.txt"
