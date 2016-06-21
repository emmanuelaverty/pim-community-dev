@javascript
Feature: Validate number attributes of a draft
  In order to keep my data consistent
  As a regular user
  I need to be able to see validation errors for number attributes

  Background:
    Given the "clothing" catalog configuration
    And the following attributes:
      | code       | label-en_US | type   | scopable | unique | negative_allowed | decimals_allowed | number_min | number_max | group |
      | ref        | Reference   | number | no       | yes    | no               | no               |            |            | info  |
      | sold       | Sold        | number | no       | no     | no               | no               |            |            | info  |
      | available  | Available   | number | yes      | no     | no               | no               |            |            | info  |
      | note       | Rating      | number | no       | no     | no               | no               | 1          | 5          | info  |
      | quality    | Quality     | number | no       | no     | no               | yes              | 1          | 10         | info  |
      | popularity | Popularity  | number | yes      | no     | no               | no               | 1          | 10         | info  |
    And the following family:
      | code | label-en_US | attributes                                           |
      | baz  | Baz         | sku, ref, sold, available, note, popularity, quality |
    And the following products:
      | sku | family | popularity-mobile | popularity-tablet | note | categories        | ref |
      | foo | baz    | 4                 | 4                 | 1    | summer_collection |     |
      | bar | baz    | 4                 | 4                 | 2    | summer_collection | 111 |
    And I am logged in as "Mary"
    And I am on the "foo" product page

  Scenario: Validate the unique constraint of number attribute
    Given I change the Reference to "111"
    And I save the product
    Then I should see validation error "The value 111 is already set on another product for the unique attribute ref"
    And there should be 1 error in the "Product information" tab

  Scenario: Validate the negative allowed constraint of number attribute
    Given I change the Sold to "-1"
    And I save the product
    Then I should see validation error "This value should be 0 or more."
    And there should be 1 error in the "Product information" tab

  Scenario: Validate the negative allowed constraint of scopable number attribute
    Given I change the Available for scope mobile to "-1"
    And I save the product
    Then I should see validation error "This value should be 0 or more."
    And there should be 1 error in the "Product information" tab

  Scenario: Validate the decimals allowed constraint of number attribute
    Given I change the Rating to "4.5"
    And I save the product
    Then I should see validation error "This value should not be a decimal."
    And there should be 1 error in the "Product information" tab

  Scenario: Validate the decimals allowed constraint of scopable number attribute
    Given I change the Popularity for scope mobile to "9.5"
    And I save the product
    Then I should see validation error "This value should not be a decimal."
    And there should be 1 error in the "Product information" tab

  Scenario: Validate the number min constraint of number attribute
    Given I change the Rating to "0"
    And I save the product
    Then I should see validation error "This value should be 1 or more."
    And there should be 1 error in the "Product information" tab

  Scenario: Validate the number min constraint of scopable number attribute
    Given I change the Popularity for scope mobile to "0"
    And I save the product
    Then I should see validation error "This value should be 1 or more."
    And there should be 1 error in the "Product information" tab

  Scenario: Validate the number max constraint of number attribute
    Given I change the Rating to "6"
    And I save the product
    Then I should see validation error "This value should be 5 or less."
    And there should be 1 error in the "Product information" tab

  Scenario: Validate the number max constraint of scopable number attribute
    Given I change the Popularity for scope mobile to "11"
    And I save the product
    Then I should see validation error "This value should be 10 or less."
    And there should be 1 error in the "Product information" tab

  Scenario: Validate the type constraint of decimal attribute
    Given I change the Quality to "qux"
    And I save the product
    Then I should see validation error "This value should be a valid number."
    And there should be 1 error in the "Product information" tab

  Scenario: Validate the type constraint of number attribute
    Given I change the Rating to "qux"
    And I save the product
    Then I should see validation error "This value should be a valid number."
    And there should be 1 error in the "Product information" tab
