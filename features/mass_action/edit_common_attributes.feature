@javascript
Feature: Apply permissions for an attribute group when mass edit common attributes
  In order to be able to only edit the product data I have access
  As a product manager
  I need to be able to mass edit only attributes I have access

  Background:
    Given a "footwear" catalog configuration
    And the following family:
      | code       | attributes                                     |
      | high_heels | sku,name,manufacturer,description,price,rating |
    And the following product:
      | sku       | family     | rating |
      | highheels | high_heels | 2      |
    And the following attribute group accesses:
      | attribute group | user group | access |
      | info            | Manager    | edit   |
      | marketing       | Manager    | view   |
    And I am logged in as "Julia"
    And I am on the products page

  Scenario: Successfully display read only attributes
    Given I select rows highheels
    And I press "Change product information" on the "Bulk Actions" dropdown button
    And I choose the "Edit common attributes" operation
    Then I should see available attributes Name, Manufacturer and Description in group "Product information"
    And I should see available attributes Price and Rating in group "Marketing"
    When I display the Price, Name and Rating attribute
    And the fields Price and Rating should be disabled
    And I visit the "Product information" group
    And I change the "Name" to "My product"
    And I move on to the next step
    And I wait for the "edit-common-attributes" mass-edit job to finish
    And I should see the text "Clean temporary files"
    And I edit the "highheels" product
    And I visit the "Marketing" group
    Then I should see the text "2 stars"
