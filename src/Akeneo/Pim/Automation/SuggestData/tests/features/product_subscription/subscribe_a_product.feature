@acceptance-back
Feature: Subscribe a product to PIM.ai
  In order to automatically enrich my products
  As Julia
  I want to subscribe a product to PIM.ai

  Scenario: Successfully subscribe a product to PIM.ai
    Given the following attribute:
      | code | type                   |
      | ean  | pim_catalog_text       |
      | sku  | pim_catalog_identifier |
    And the following family:
      | code   | attributes | label-en_US |
      | tshirt | sku,ean    | T-Shirt     |
    And the following product:
      | identifier | family | ean          |
      | ts_0013    | tshirt | 606449099812 |
    And a predefined mapping as follows:
      | pim_ai_code | attribute_code |
      | upc         | ean            |
    When I subscribe the product "ts_0013" to PIM.ai
    Then the product "ts_0013" should be subscribed

  Scenario: Fail to subscribe a product without family
    Given the following attribute:
      | code | type                   |
      | ean  | pim_catalog_text       |
      | sku  | pim_catalog_identifier |
    And the following product:
      | identifier             | family | ean          |
      | product_without_family |        | 606449099812 |
    And a predefined mapping as follows:
      | pim_ai_code | attribute_code |
      | upc         | ean            |
    When I subscribe the product "product_without_family" to PIM.ai
    Then the product "product_without_family" should not be subscribed

  Scenario: Fail to subscribe a product that does not have any values on mapped identifiers
    Given the following attribute:
      | code | type                   |
      | sku  | pim_catalog_identifier |
      | ean  | pim_catalog_text       |
    And the following family:
      | code   | attributes | label-en_US |
      | tshirt | sku,ean    | T-Shirt     |
    And the following product:
      | identifier             | family |
      | product_without_values | tshirt |
    And a predefined mapping as follows:
      | pim_ai_code | attribute_code |
      | upc         | ean            |
    When I subscribe the product "product_without_values" to PIM.ai
    Then the product "product_without_values" should not be subscribed

  Scenario: Fail to subscribe a product that is already subscribed to PIM.ai
    Given the following attribute:
      | code | type                   |
      | ean  | pim_catalog_text       |
      | sku  | pim_catalog_identifier |
    And the following family:
      | code   | attributes | label-en_US |
      | tshirt | sku,ean    | T-Shirt     |
    And a predefined mapping as follows:
      | pim_ai_code | attribute_code |
      | upc         | ean            |
    And PIM.ai is configured with a valid token
    And the following product subscribed to pim.ai:
      | identifier | family | ean          |
      | ts_0013    | tshirt | 606449099812 |
    When I subscribe the product "ts_0013" to PIM.ai
    Then the product "ts_0013" should be subscribed

  Scenario: Fail to subscribe a product with an invalid token
    Given the following attribute:
      | code | type                   |
      | ean  | pim_catalog_text       |
      | sku  | pim_catalog_identifier |
    And the following family:
      | code   | attributes | label-en_US |
      | tshirt | sku,ean    | T-Shirt     |
    And the following product:
      | identifier | family | ean          |
      | ts_0013    | tshirt | 606449099812 |
    And a predefined mapping as follows:
      | pim_ai_code | attribute_code |
      | upc         | ean            |
    And the PIM.ai token is expired
    When I subscribe the product "ts_0013" to PIM.ai
    Then the product "ts_0013" should not be subscribed

  Scenario: Subscribe a product without enough money on PIM.ai account
    Given the following attribute:
      | code | type                   |
      | ean  | pim_catalog_text       |
      | sku  | pim_catalog_identifier |
    And the following family:
      | code   | attributes | label-en_US |
      | tshirt | sku,ean    | T-Shirt     |
    And the following product:
      | identifier | family | ean          |
      | ts_0013    | tshirt | 606449099812 |
    And a predefined mapping as follows:
      | pim_ai_code | attribute_code |
      | upc         | ean            |
    And there are no more credits on my PIM.ai account
    When I subscribe the product "ts_0013" to PIM.ai
    Then the product "ts_0013" should not be subscribed

  #Scenario: Fail to subscribe a product that does not exist

  #Scenario: Fail to subscribe a product that has an incorrect UPC
  # wrong UPC format

  Scenario: Fail to subscribe a product that does not have MPN and Brand filled together
    Given the following attribute:
      | code  | type                   |
      | mpn   | pim_catalog_text       |
      | brand | pim_catalog_text       |
      | sku   | pim_catalog_identifier |
    And the following family:
      | code   | attributes    | label-en_US |
      | tshirt | sku,mpn,brand | T-Shirt     |
    And the following product:
      | identifier | family | mpn         |
      | ts_0013    | tshirt | tshirt-1002 |
    And a predefined mapping as follows:
      | pim_ai_code | attribute_code |
      | mpn         | mpn            |
      | brand       | brand          |
    When I subscribe the product "ts_0013" to PIM.ai
    Then the product "ts_0013" should not be subscribed

  #Scenario: Handle a bad request to PIM.ai
