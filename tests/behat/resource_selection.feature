@local @local_coursebridge @javascript
Feature: Reuse mixed materials in native File settings
  In order to add supporting materials without replacing the main file
  As a destination course teacher
  I need native saving and cancellation to remain in control

  Background:
    Given the following "local_coursebridge > folder fixtures" exist:
      | name                |
      | Resource acceptance |
    And I am on the "Target resource" "resource activity" page logged in as "cbteacher"
    And I navigate to "Settings" in current page administration
    And I press "Select several files or folders"

  Scenario: Add a folder and a source main file and save supporting files
    When I set the field "Files and folders" to "Folder: CB2025: Published materials/, File: CB2025: Published resource/resource.txt"
    And I press "Review selection"
    Then I should see "notes2025.txt"
    When I press "Add reviewed materials"
    Then I should see "Materials added to the draft"
    And I should see "4" elements in "Select files" filemanager
    When I press "Save and display"
    Then I should see "Target resource"
    When I navigate to "Settings" in current page administration
    Then I should see "4" elements in "Select files" filemanager

  Scenario: Cancel leaves the saved File activity unchanged
    When I set the field "Files and folders" to "Folder: CB2025: Published materials/"
    And I press "Review selection"
    And I press "Add reviewed materials"
    And I press "Cancel"
    And I am on the "Target resource" "resource activity" page
    And I navigate to "Settings" in current page administration
    Then I should see "1" elements in "Select files" filemanager
