@javascript
Feature: Edit an asset category
  In order to be able to modify the asset category tree
  As an asset manager
  I need to be able to edit an asset category

  Background:
    Given a "clothing" catalog configuration
    And I am logged in as "Pamela"

  Scenario: Successfully edit an asset category
    Given I edit the "images" asset category
    Then I should see the Code field
    And the field Code should be disabled
    When I fill in the following information:
      | English (United States) | My images |
    And I save the category
    And I should see the flash message "Category successfully updated"
    Then I should be on the asset category "images" edit page
    And I should see "My images"

  Scenario: Go to category edit page from the asset category tree
    Given I am on the assets categories page
    And I select the "Asset main catalog" tree
    And I click on the "videos" category
    Then I should be on the asset category "videos" edit page

  @skip-nav
  Scenario: Successfully display a dialog when we quit a page with unsaved changes
    Given I edit the "client_documents" asset category
    When I fill in the following information:
      | English (United States) | 2015 Clients documents |
    And I click on the Akeneo logo
    Then I should see "You will lose changes to the category if you leave the page." in popup

  @skip
  Scenario: Successfully display a message when there are unsaved changes
    Given I edit the "images" asset category
    When I fill in the following information:
      | English (United States) | My images |
    Then I should see "There are unsaved changes."

  Scenario: Stay on the asset category when I save it and keep category tree open (without oro nav)
    Given I edit the "situ" asset category
    When I fill in the following information:
      | English (United States) | Situ |
    And I save the category
    Then I should see the flash message "Category successfully updated"
    And I should be on the asset category "situ" edit page
    And I should see the text "Situ"
    And I should see the text "Prioritised images"

  Scenario: Stay on the asset category when I save it and keep category tree open (with oro nav)
    Given I am on the assets categories page
    And I expand the "images" category
    And I click on the "situ" category
    When I fill in the following information:
      | English (United States) | Situ |
    And I save the category
    Then I should see the flash message "Category successfully updated"
    And I should be on the asset category "situ" edit page
    And I should see the text "Situ"
    And I should see the text "Prioritised images"
