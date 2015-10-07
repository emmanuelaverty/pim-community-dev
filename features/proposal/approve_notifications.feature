@javascript
Feature: Approve notifications
  In order to easily quickly now if my proposals have been reviewed
  As a proposal redactor
  I need to be able to see a notification when the owner approve a proposal

  Background:
    Given an "clothing" catalog configuration
    And the following product category accesses:
      | product category | user group | access |
      | 2014_collection  | Redactor   | edit   |
    And the following products:
      | sku     | family   | categories      |
      | tshirt  | jackets  | 2014_collection |
    And Mary proposed the following change to "tshirt":
      | field | value          |
      | Name  | Summer t-shirt |

  Scenario: A notification is sent when I approve a proposal from the proposal grid
    Given I am logged in as "Peter"
    And I am on the proposals page
    And I click on the "Approve" action of the row which contains "Summer t-shirt"
    And I press the "Yes, do it" button in the popin
    When I logout
    And I am logged in as "Mary"
    And I am on the dashboard page
    Then I should have 1 new notification
    And I should see notification:
      | type    | message                                                          |
      | success | Peter Williams has accepted your proposal for the product tshirt |
    When I click on the notification "Peter Williams has accepted your proposal for the product tshirt"
    Then I should be on the product "tshirt" edit page

  Scenario: A notification is sent when I approve a proposal from the proposal grid with a comment
    Given I am logged in as "Peter"
    And I am on the proposals page
    And I click on the "Approve" action of the row which contains "Summer t-shirt"
    And I fill in the following information in the popin:
      | Comment | You did a nice job on this proposal. Thank you ! |
    And I press the "Yes, do it" button in the popin
    When I logout
    And I am logged in as "Mary"
    And I am on the dashboard page
    Then I should have 1 new notification
    And I should see notification:
      | type    | message                                                          | comment                                          |
      | success | Peter Williams has accepted your proposal for the product tshirt | You did a nice job on this proposal. Thank you ! |
    When I click on the notification "Peter Williams has accepted your proposal for the product tshirt"
    Then I should be on the product "tshirt" edit page

  Scenario: A notification is sent when I approve a proposal from the product draft page
    Given I am logged in as "Peter"
    And I edit the "tshirt" product
    And I visit the "Proposals" tab
    And I click on the "Approve" action of the row which contains "Summer t-shirt"
    And I press the "Yes, do it" button in the popin
    When I logout
    And I am logged in as "Mary"
    And I am on the dashboard page
    Then I should have 1 new notification
    And I should see notification:
      | type    | message                                                          |
      | success | Peter Williams has accepted your proposal for the product tshirt |
    When I click on the notification "Peter Williams has accepted your proposal for the product tshirt"
    Then I should be on the product "tshirt" edit page

  Scenario: A notification is sent when I approve a proposal from the product draft page
    Given I am logged in as "Peter"
    And I edit the "tshirt" product
    And I visit the "Proposals" tab
    And I click on the "Approve" action of the row which contains "Summer t-shirt"
    And I fill in the following information in the popin:
      | Comment | You did a nice job on this proposal. Thank you ! |
    And I press the "Yes, do it" button in the popin
    When I logout
    And I am logged in as "Mary"
    And I am on the dashboard page
    Then I should have 1 new notification
    And I should see notification:
      | type    | message                                                          | comment                                          |
      | success | Peter Williams has accepted your proposal for the product tshirt | You did a nice job on this proposal. Thank you ! |
    When I click on the notification "Peter Williams has accepted your proposal for the product tshirt"
    Then I should be on the product "tshirt" edit page

  Scenario: A notification is sent when I approve a proposal from mass approval
    Given I am logged in as "Peter"
    And I am on the proposals page
    And I press the "All" button
    And I follow "Approve selected"
    And I press the "Yes, do it" button in the popin
    When I logout
    And I am logged in as "Mary"
    And I am on the dashboard page
    Then I should have 1 new notification
    And I should see notification:
      | type    | message                                                          |
      | success | Peter Williams has accepted your proposal for the product tshirt |
    When I click on the notification "Peter Williams has accepted your proposal for the product tshirt"
    Then I should be on the product "tshirt" edit page

  Scenario: A notification is sent when I approve a proposal from mass approval
    Given I am logged in as "Peter"
    And I am on the proposals page
    And I press the "All" button
    And I follow "Approve selected"
    And I fill in the following information in the popin:
      | Comment | You did a nice job on this proposal. Thank you ! |
    And I press the "Yes, do it" button in the popin
    When I logout
    And I am logged in as "Mary"
    And I am on the dashboard page
    Then I should have 1 new notification
    And I should see notification:
      | type    | message                                                          | comment                                          |
      | success | Peter Williams has accepted your proposal for the product tshirt | You did a nice job on this proposal. Thank you ! |
    When I click on the notification "Peter Williams has accepted your proposal for the product tshirt"
    Then I should be on the product "tshirt" edit page
