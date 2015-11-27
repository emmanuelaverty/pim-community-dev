@javascript
Feature: Browse smart attributes in the attribute grid
  In order to know which attributes are smart
  As a regular user
  I need to see and filter by the smart property

  Background:
    Given a "footwear" catalog configuration
    And I am logged in as "Julia"

  @deprecated
  Scenario: Successfully display the smart column in the attribute grid
    Given I am on the attributes page
    Then I should see the columns Code, Label, Type, Scopable, Localizable, Group and Smart

  @deprecated
  Scenario: Successfully filter by the smart property in the attribute grid
    Given I am on the attributes page
    And the following product rule definitions:
      """
      rule1:
        priority: 10
        conditions:
          - field:    sku
            operator: =
            value: camcorders
        actions:
          - type:  set_value
            field: name
            value: Foo
            locale: en_US
      """
    When I filter by "Type" with value "Text"
    Then I should be able to use the following filters:
      | filter | value | result       |
      | Smart  | yes   | name         |
      | Smart  | no    | 123, comment |

  @deprecated @info https://akeneo.atlassian.net/browse/PIM-5056
  Scenario: Successfully display the correct amount of smart attribute on grid
    Given I am on the attributes page
    And the following product rule definitions:
      """
      rule1:
        priority: 10
        conditions:
          - field:    sku
            operator: =
            value: camcorders
        actions:
          - type:  set_value
            field: name
            value: Foo
            locale: en_US
          - type:  set_value
            field: comment
            value: Foo
          - type:  set_value
            field: description
            value: Foo
            locale: en_US
            scope: mobile
          - type:  set_value
            field: handmade
            value: true
          - type:  set_value
            field: length
            value:
              data: 10
              unit: CENTIMETER
          - type:  set_value
            field: price
            value:
              - data: 2
                currency: EUR
          - type:  set_value
            field: number_in_stock
            value: 2
          - type:  set_value
            field: destocking_date
            value: "2015-05-26"
      """
    And the product rule "rule1" is executed
    When I filter by "Smart" with value "yes"
    Then the grid should contain 8 elements
