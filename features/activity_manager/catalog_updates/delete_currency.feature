@javascript
Feature: Catalog updates - Remove a currency used as project filter
  In order to manage product catalog
  As a user
  I need to remove a currency from channel

  Background:
    Given the "activity_manager" catalog configuration
    And the following attribute groups:
      | code      | label-en_US |
      | marketing | Marketing   |
      | technical | Technical   |
      | other     | Other       |
    And the following attributes:
      | code        | label-en_US | type                         | localizable | scopable | decimals_allowed | metric_family | default metric unit | useable_as_grid_filter | group     | allowed extensions |
      | sku         | SKU         | pim_catalog_identifier       | 0           | 0        |                  |               |                     | 1                      | other     |                    |
      | name        | Name        | pim_catalog_text             | 1           | 0        |                  |               |                     | 1                      | marketing |                    |
      | description | Description | pim_catalog_text             | 1           | 1        |                  |               |                     | 0                      | marketing |                    |
      | size        | Size        | pim_catalog_text             | 1           | 0        |                  |               |                     | 1                      | marketing |                    |
      | price       | Price       | pim_catalog_price_collection | 0           | 0        |                  |               |                     | 1                      | marketing |                    |
    And the following attribute group accesses:
      | attribute group | user group | access |
      | marketing       | All        | view   |
      | marketing       | All        | edit   |
      | other           | All        | view   |
      | other           | All        | edit   |
    And the following categories:
      | code     | label-en_US | parent  |
      | clothing | Clothing    | default |
    And the following families:
      | code   | label-en_US | attributes                       | requirements-ecommerce    | requirements-mobile       |
      | tshirt | TShirts     | sku,name,description,size ,price | sku,name,size,description | sku,name,size,description |
    And the following products:
      | sku                  | family | categories | name-en_US                | size-en_US | price     |
      | tshirt-the-witcher-3 | tshirt | clothing   | T-Shirt "The Witcher III" | M          | 10.00 USD |
      | tshirt-skyrim        | tshirt | clothing   | T-Shirt "Skyrim"          | M          | 10.00 USD |
      | tshirt-lcd           | tshirt | clothing   | T-shirt LCD screen        | M          | 10.00 USD |
    And the following projects:
      | label                  | owner | due_date   | description                                  | channel   | locale | product_filters                                                                |
      | Collection Summer 2030 | julia | 2030-10-28 | Please do your best to finish before Summer. | ecommerce | en_US  | [{"field":"price", "operator":"=", "value": {"amount": 5, "currency": "USD"}}] |
      | Collection Winter 2030 | julia | 2030-08-28 | Please do your best to finish before Winter. | mobile    | en_US  | [{"field":"family.code", "operator":"IN", "value": ["tshirt"]}]                |
    And I am logged in as "Julia"

  Scenario: Remove a currency used as project filter
    Given I am on the "ecommerce" channel page
    When I fill in the following information:
      | Currencies | EUR |
    And I press the "Save" button
    Then I should see the text "Channel successfully updated"
    When I am on the dashboard page
    Then I should not see the "Collection Summer 2030" project in the widget
    But I should see the "Collection Winter 2030" project in the widget
    When I am on the products page
    And I switch view selector type to "Projects"
    Then I should not see the "Collection Summer 2030" project
    But I should see the "Collection Winter 2030" project
