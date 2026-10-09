@local @local_coursebridge @javascript @accessibility
Feature: Review mixed materials in the native folder settings form
  In order to reuse published editions together
  As a destination course teacher
  I need to retain my pending changes and save or cancel normally

  Background:
    Given the following "local_coursebridge > folder fixtures" exist:
      | name              |
      | Folder acceptance |
    And I am on the "Target materials" "folder activity" page logged in as "cbteacher"
    And I press "Edit"
    And I navigate to "Reuse related materials" in current page administration

  Scenario: Review two folders and a resource, retain selection during filtering, and save
    When I set the field "Name" to "Pending folder title"
    And I set the field "Files and folders" to "Folder: CB2024: Published materials/, Folder: CB2025: Published materials/, File: CB2025: Published resource/resource.txt"
    And I set the field "File name" to "resource"
    And I press "Search materials"
    Then the field "Name" matches value "Pending folder title"
    When I press "Review selection"
    Then I should see "notes2024.txt"
    And I should see "notes2025.txt"
    And I should see "Original teacher"
    When I press "Add reviewed materials"
    Then I should see "Materials added to the draft"
    And the ".coursebridge-selection-review" "css_element" should meet accessibility standards
    And I should see "4" elements in "Files" filemanager
    When I press "Save and display"
    Then I should see "Pending folder title"
    And I should see "existing.txt"
    And I should see "lesson.txt"
    And I should see "resource.txt"
    And I should see "notes2024.txt"
    And I should see "notes2025.txt"

  Scenario: Cancel discards copied draft materials and pending title
    When I set the field "Name" to "Cancelled title"
    And I set the field "Files and folders" to "Folder: CB2025: Published materials/, File: CB2025: Published resource/resource.txt"
    And I press "Review selection"
    And I press "Add reviewed materials"
    And I press "Cancel"
    And I am on the "Target materials" "folder activity" page
    Then I should see "existing.txt"
    And I should not see "lesson.txt"
    And I should not see "resource.txt"
    And I should not see "Cancelled title"
