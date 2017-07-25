@javascript
Feature: View products via widget completeness links
  In order to display detailed products from the widget completeness
  As a contributor or project owner
  I need to be able to click on them and display a filtered grid

  Background:
    Given the "teamwork_assistant" catalog configuration
    And the following attribute groups:
      | code      | label-en_US |
      | marketing | Marketing   |
      | technical | Technical   |
      | other     | Other       |
      | media     | Media       |
    And the following attributes:
      | code         | label-en_US  | type                   | localizable | scopable | decimals_allowed | negative_allowed | metric_family | default_metric_unit | useable_as_grid_filter | group     | allowed_extensions |
      | sku          | SKU          | pim_catalog_identifier | 0           | 0        |                  |                  |               |                     | 1                      | other     |                    |
      | name         | Name         | pim_catalog_text       | 1           | 0        |                  |                  |               |                     | 1                      | marketing |                    |
      | description  | Description  | pim_catalog_text       | 1           | 1        |                  |                  |               |                     | 0                      | marketing |                    |
      | size         | Size         | pim_catalog_text       | 1           | 0        |                  |                  |               |                     | 1                      | marketing |                    |
      | weight       | Weight       | pim_catalog_metric     | 1           | 0        | 0                | 0                | Weight        | GRAM                | 1                      | technical |                    |
      | release_date | Release date | pim_catalog_date       | 1           | 0        |                  |                  |               |                     | 1                      | other     |                    |
      | capacity     | Capacity     | pim_catalog_metric     | 0           | 0        | 0                | 0                | Binary        | GIGABYTE            | 1                      | technical |                    |
      | material     | Material     | pim_catalog_text       | 1           | 0        |                  |                  |               |                     | 1                      | technical |                    |
      | picture      | Picture      | pim_catalog_image      | 0           | 1        |                  |                  |               |                     | 0                      | media     | jpg                |
    And the following categories:
      | code       | label-en_US | parent  |
      | clothing   | Clothing    | default |
      | high_tech  | High-Tech   | default |
      | decoration | Decoration  | default |
    And the following product category accesses:
      | product category | user group          | access |
      | clothing         | Marketing           | own    |
      | clothing         | Technical Clothing  | edit   |
      | clothing         | Technical High-Tech | none   |
      | clothing         | Read Only           | view   |
      | clothing         | Media manager       | edit   |
      | high_tech        | Marketing           | edit   |
      | high_tech        | Technical Clothing  | view   |
      | high_tech        | Technical High-Tech | edit   |
      | high_tech        | Read Only           | view   |
      | high_tech        | Media manager       | edit   |
      | decoration       | Marketing           | edit   |
      | decoration       | Technical Clothing  | none   |
      | decoration       | Technical High-Tech | none   |
      | decoration       | Read Only           | view   |
      | decoration       | Media manager       | edit   |
    And the following attribute group accesses:
      | attribute group | user group          | access | group | type             |
      | marketing       | Marketing           | edit   | other | pim_catalog_text |
      | marketing       | Technical Clothing  | view   | other | pim_catalog_text |
      | marketing       | Technical High-Tech | view   | other | pim_catalog_text |
      | marketing       | Read Only           | view   | other | pim_catalog_text |
      | marketing       | Media manager       | view   | other | pim_catalog_text |
      | technical       | Marketing           | view   | other | pim_catalog_text |
      | technical       | Technical Clothing  | edit   | other | pim_catalog_text |
      | technical       | Technical High-Tech | edit   | other | pim_catalog_text |
      | technical       | Read Only           | view   | other | pim_catalog_text |
      | technical       | Media manager       | none   | other | pim_catalog_text |
      | other           | Marketing           | edit   | other | pim_catalog_text |
      | other           | Technical Clothing  | edit   | other | pim_catalog_text |
      | other           | Technical High-Tech | edit   | other | pim_catalog_text |
      | other           | Read Only           | view   | other | pim_catalog_text |
      | other           | Media manager       | view   | other | pim_catalog_text |
      | media           | Marketing           | view   | other | pim_catalog_text |
      | media           | Technical Clothing  | view   | other | pim_catalog_text |
      | media           | Technical High-Tech | view   | other | pim_catalog_text |
      | media           | Read Only           | view   | other | pim_catalog_text |
      | media           | Media manager       | edit   | other | pim_catalog_text |
    And the following families:
      | code     | label-en_US | attributes                                                     | requirements-ecommerce                     | requirements-mobile                |
      | tshirt   | TShirts     | sku,name,description,size,weight,release_date,material         | sku,name,size,description,material         | sku,name,size,description,material |
      | usb_keys | USB Keys    | sku,name,description,size,weight,release_date,capacity,picture | sku,name,size,description,capacity,picture | sku,name,size,description,capacity |
      | posters  | Posters     | sku,name,description,size,release_date,picture                 | sku,name,size,description,picture          | sku,name,size,description,picture  |
    And the following products:
      | sku                  | family   | categories         | name-en_US                | size-en_US | weight-en_US | weight-en_US-unit | release_date-en_US | release_date-fr_FR | material-en_US | capacity | capacity-unit |
      | tshirt-the-witcher-3 | tshirt   | clothing           | T-Shirt "The Witcher III" | M          | 5            | OUNCE             | 2015-06-20         | 2015-06-20         | cotton         |          |               |
      | tshirt-skyrim        | tshirt   | clothing           | T-Shirt "Skyrim"          | M          | 5            | OUNCE             |                    |                    |                |          |               |
      | tshirt-lcd           | tshirt   | clothing,high_tech | T-shirt LCD screen        | M          | 6            | OUNCE             | 2016-08-13         |                    |                |          |               |
      | usb-key-big          | usb_keys | high_tech          | USB Key Big 64Go          |            | 1            | OUNCE             | 2016-08-13         | 2016-10-13         |                |          |               |
      | usb-key-small        | usb_keys | high_tech          |                           |            | 1            | OUNCE             |                    |                    |                | 8        | GIGABYTE      |
      | poster-movie-contact | posters  | decoration         | Movie poster "Contact"    | A1         |              |                   |                    |                    |                |          |               |
    And I am logged in as "Mary"
    And I am on the products page
    And I filter by "category" with operator "" and value "clothing"
    And I show the filter "weight"
    And I filter by "weight" with operator "<" and value "6 Ounce"
    And I click on the create project button
    When I fill in the following information in the popin:
      | project-label       | 2016 summer collection |
      | project-description | 2016 summer collection |
      | project-due-date    | 12/13/2018             |
    And I press the "Save" button
    Then I should be on the products page
    And I go on the last executed job resume of "project_calculation"
    And I wait for the "project_calculation" job to finish
    And I logout

  Scenario: A contributor can display his products todo, in progress and done
    Given I am logged in as "Julia"
    And I am on the dashboard page
    When I click on the "todo" section of the teamwork assistant widget
    Then I should be on the products page
    And the criteria of "project_completeness" filter should be "Todo"
    When I am on the dashboard page
    And I click on the "in-progress" section of the teamwork assistant widget
    Then I should be on the products page
    And the criteria of "project_completeness" filter should be "In progress"
    When I am on the dashboard page
    And I click on the "done" section of the teamwork assistant widget
    Then I should be on the products page
    And the criteria of "project_completeness" filter should be "Done"

  Scenario: A project owner can display project products todo, in progress and done
    Given I am logged in as "Mary"
    And I am on the dashboard page
    When I click on the "todo" section of the teamwork assistant widget
    Then I should be on the products page
    And the criteria of "project_completeness" filter should be "Todo (project overview)"
    When I am on the dashboard page
    And I click on the "in-progress" section of the teamwork assistant widget
    Then I should be on the products page
    And the criteria of "project_completeness" filter should be "In progress (project overview)"
    When I am on the dashboard page
    And I click on the "done" section of the teamwork assistant widget
    Then I should be on the products page
    And the criteria of "project_completeness" filter should be "Done (project overview)"

  Scenario: A project owner can display his products todo, in progress and done
    Given I am logged in as "Mary"
    And I am on the dashboard page
    And I select "Mary" contributor
    Then I should see the text "2016 summer collection E-Commerce | English (United States)"
    And I should see the text "Mary Smith"
    When I click on the "todo" section of the teamwork assistant widget
    Then I should be on the products page
    And the criteria of "project_completeness" filter should be "Todo"
    When I am on the dashboard page
    And I select "Mary" contributor
    Then I should see the text "2016 summer collection E-Commerce | English (United States)"
    And I should see the text "Mary Smith"
    And I click on the "in-progress" section of the teamwork assistant widget
    Then I should be on the products page
    And the criteria of "project_completeness" filter should be "In progress"
    When I am on the dashboard page
    And I select "Mary" contributor
    Then I should see the text "2016 summer collection E-Commerce | English (United States)"
    And I should see the text "Mary Smith"
    And I click on the "done" section of the teamwork assistant widget
    Then I should be on the products page
    And the criteria of "project_completeness" filter should be "Done"