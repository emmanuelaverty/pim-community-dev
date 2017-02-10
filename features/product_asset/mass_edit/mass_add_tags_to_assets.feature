@javascript
Feature: Mass edit assets to add them tags
  In order to add the same tags to many assets
  As a product manager
  I need to be able to mass edit assets to add them tags

  Background:
    Given a "default" catalog configuration
    And the following assets:
      | code        |
      | asset_one   |
      | asset_two   |
      | asset_three |
    And the following assets tags:
      | code       |
      | first_tag  |
      | second_tag |
      | third_tag  |
    And I am logged in as "Pamela"
    And I am on the assets page

  Scenario: Massively add existing tags to all assets with a bulk action from the grid
    Given I select all entities
    And I press "Add tags" on the "Bulk Actions" dropdown button
    And I set the current page to "Batch AddTags"
    When I fill in "Tags" with "first_tag, second_tag, third_tag" on the current page
    And I press the "Next" button
    And I press the "Confirm" button
    And I should be on the assets page
    And I wait for the "add-tags-to-assets" mass-edit job to finish
    Then asset tags of "asset_one" should be "first_tag, second_tag, third_tag"
    And asset tags of "asset_two" should be "first_tag, second_tag, third_tag"
    And asset tags of "asset_three" should be "first_tag, second_tag, third_tag"

  Scenario: Massively add exiting tags to several assets with a bulk action from the grid
    Given I select rows asset_one and asset_two
    And I press "Add tags" on the "Bulk Actions" dropdown button
    And I set the current page to "Batch AddTags"
    When I fill in "Tags" with "first_tag, second_tag, third_tag" on the current page
    And I press the "Next" button
    And I press the "Confirm" button
    And I should be on the assets page
    And I wait for the "add-tags-to-assets" mass-edit job to finish
    Then asset tags of "asset_one" should be "first_tag, second_tag, third_tag"
    And asset tags of "asset_two" should be "first_tag, second_tag, third_tag"
    And asset tags of "asset_three" should be ""

  Scenario: Massively add new tags to several assets with a bulk action from the grid
    Given I select rows asset_one and asset_two
    And I press "Add tags" on the "Bulk Actions" dropdown button
    And I set the current page to "Batch AddTags"
    When I fill in "Tags" with "new_tag, another_new_tag" on the current page
    And I press the "Next" button
    And I press the "Confirm" button
    And I should be on the assets page
    And I wait for the "add-tags-to-assets" mass-edit job to finish
    Then asset tags of "asset_one" should be "another_new_tag, new_tag"
    And asset tags of "asset_two" should be "another_new_tag, new_tag"
    And asset tags of "asset_three" should be ""

  Scenario: Massively add new and existing tags to several assets with a bulk action from the grid
    Given I select rows asset_one and asset_two
    And I press "Add tags" on the "Bulk Actions" dropdown button
    And I set the current page to "Batch AddTags"
    When I fill in "Tags" with "new_tag, another_new_tag, third_tag" on the current page
    And I press the "Next" button
    And I press the "Confirm" button
    And I should be on the assets page
    And I wait for the "add-tags-to-assets" mass-edit job to finish
    Then asset tags of "asset_one" should be "another_new_tag, new_tag, third_tag"
    And asset tags of "asset_two" should be "another_new_tag, new_tag, third_tag"
    And asset tags of "asset_three" should be ""