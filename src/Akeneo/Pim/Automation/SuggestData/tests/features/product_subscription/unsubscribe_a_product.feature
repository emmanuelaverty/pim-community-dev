@acceptance-back
Feature: Unsubscribe a product to PIM.ai
  In order to manage the products I subscribed to
  As Julia
  I want to unsubscribe a product to PIM.ai

  Scenario: Successfully unsubscribe a product to PIM.ai
    Given the product "B00F0DD0I6" of the family "router"
    And the product "B00F0DD0I6" is subscribed to PIM.ai
    When I unsubscribe the product "B00F0DD0I6"
    Then the product "B00F0DD0I6" should not be subscribed

  Scenario: Failed to unsubscribe a product with an invalid token
    Given the product "B00F0DD0I6" of the family "router"
    And the product "B00F0DD0I6" is subscribed to PIM.ai
    And the PIM.ai token is expired
    When I unsubscribe the product "B00F0DD0I6"
    Then the product "B00F0DD0I6" should be subscribed
