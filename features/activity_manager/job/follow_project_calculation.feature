Feature: Follow project calculation job
  In order to see project calculation progress
  As a project creator
  I need to be able to see the project calculation job in the process tracker

  @javascript
  Scenario: A project creator can see the project calculation job in the process tracker
    Given the "activity_manager" catalog configuration
    And I am logged in as "Julia"
    When I am on the products page
    And I open the view selector
    And I click on "Create project" action in the dropdown
    And I fill in the following information in the popin:
      | project-label       | Collection 2017                 |
      | project-description | My very awesome collection 2007 |
      | project-due-date    | 05/12/2117                      |
    And I press the "Save" button
    Then I should be on the products page
    When I am on the job tracker page
    Then I should see entity Project calculation
    And the grid should contain 1 element
    When I click on the "Show" action of the row which contains "project_calculation"
    Then I should see the text "execution details - Project calculation [project_calculation]"
