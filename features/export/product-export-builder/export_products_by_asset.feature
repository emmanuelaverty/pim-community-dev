@javascript
Feature: Export products according to multi select reference data values
  In order to use the enriched product data
  As a product manager
  I need to be able to export the products according to their reference data values

  Background:
    Given the "clothing" catalog configuration
    And the following products:
      | sku     | front_view |
      | shirt-1 | akene      |
      | shirt-2 | dog        |
      | shirt-3 |            |
    And the following jobs:
      | connector            | type   | alias              | code               | label              |
      | Akeneo CSV Connector | export | csv_product_export | csv_product_export | CSV product export |
    And the following job "csv_product_export" configuration:
      | filePath | %tmp%/product_export/product_export.csv |
      | filters  | {"structure": {"locales": ["fr_FR", "en_US"], "scope": "tablet"},"data":[]} |

  # default value to fix
  @skip
  Scenario: Export only the product values with selected reference data value
    Given I am logged in as "Julia"
    And I am on the "csv_product_export" export job edit page
    And I visit the "Content" tab
    And I add available attributes Front view
    And I filter by "completeness" with operator "No condition on completeness" and value ""
    And I filter by "front_view.code" with operator "In list" and value "akene"
    And I press the "Save" button
    When I launch the export job
    And I wait for the "csv_product_export" job to finish
    Then exported file of "csv_product_export" should contain:
      """
      sku;categories;enabled;family;front_view;groups
      shirt-1;;1;;akene;
      """

  # default value to fix
  @skip
  Scenario: Export only the product values with selected reference data values
    Given I am logged in as "Julia"
    And I am on the "csv_product_export" export job edit page
    And I visit the "Content" tab
    And I add available attributes Front view
    And I filter by "completeness" with operator "No condition on completeness" and value ""
    And I filter by "front_view.code" with operator "In list" and value "dog,akene"
    And I press the "Save" button
    When I launch the export job
    And I wait for the "csv_product_export" job to finish
    Then exported file of "csv_product_export" should contain:
      """
      sku;categories;enabled;family;front_view;groups
      shirt-1;;1;;akene;
      shirt-2;;1;;dog;
      """

  # default value to fix
  @skip
  Scenario: Export only the product values without reference data values
    Given I am logged in as "Julia"
    And I am on the "csv_product_export" export job edit page
    And I visit the "Content" tab
    And I add available attributes Front view
    And I filter by "completeness" with operator "No condition on completeness" and value ""
    And I filter by "front_view.code" with operator "Is empty" and value ""
    And I press the "Save" button
    When I launch the export job
    And I wait for the "csv_product_export" job to finish
    Then exported file of "csv_product_export" should contain:
      """
      sku;categories;enabled;family;front_view;groups
      shirt-3;;1;;;
      """

  # default value to fix
  @skip
  Scenario: Export all the product values when no reference data is provided with operator IN LIST
    Given I am logged in as "Julia"
    And I am on the "csv_product_export" export job edit page
    And I visit the "Content" tab
    And I add available attributes Front view
    And I filter by "completeness" with operator "No condition on completeness" and value ""
    And I filter by "front_view.code" with operator "In list" and value ""
    And I press the "Save" button
    When I launch the export job
    And I wait for the "csv_product_export" job to finish
    Then exported file of "csv_product_export" should contain:
      """
      sku;categories;enabled;family;front_view;groups
      shirt-1;;1;;akene;
      shirt-2;;1;;dog;
      shirt-3;;1;;;
      """
