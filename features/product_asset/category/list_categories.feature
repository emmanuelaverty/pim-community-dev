@javascript
Feature: List assets categories
  In order to be able to see the categories in my catalog
  As an asset manager
  I need to be able to list existing categories

  Scenario: Successfully display assets categories
    Given a "clothing" catalog configuration
    And I am logged in as "Pamela"
    When I am on the assets categories page
    Then I should see "Asset main catalog"
    And I should see "Images"
    And I should see "Print"
    And I should see "Videos"
    And I should see "Client documents"
    And I should see "Store documents"
    And I should see "Technical documents"
    And I should see "Sales documents"
    And I should see "Archives"
    And I should see "Please select a category on the left or Create a new category"

  Scenario: Click on a asset category without the right permissions do nothing
    Given a "clothing" catalog configuration
    And I am logged in as "Peter"
    When I am on the "Administrator" role page
    And I visit the "Permissions" tab
    And I revoke rights to resources Edit an asset category and Create an asset category
    And I save the role
    Then I should not see the text "There are unsaved changes."
    When I am on the assets categories page
    Then I should not see "Please select a category on the left or Create a new category"
    When I click on the "asset_main_catalog" category
    Then I should not see "Server error"