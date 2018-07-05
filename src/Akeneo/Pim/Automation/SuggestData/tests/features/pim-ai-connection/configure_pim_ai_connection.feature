@acceptance-back
Feature: Configure the connection to PIM.ai
  In order to automatically enrich products
  As a system administrator
  I want to setup the PIM connection to PIM.ai

  Scenario: Setup the connection to PIM.ai
    When a user activates PIM.ai
    Then PIM.ai is activated

  Scenario: Reactivate the connection to PIM.ai
    Given PIM.ai is not active anymore
    When a user reactivates PIM.ai
    Then PIM.ai is activated

  Scenario: Cannot setup a connection to PIM.ai with an invalid token
    When a user tries to activate PIM.ai with an invalid activation code
    Then PIM.ai is not activated

  Scenario: Retrieve an activated connection
    Given PIM.ai was activated
    Then PIM.ai configuration can be retrieved
