Feature: Monitor catalog volume
  In order to guarantee the performance of the PIM
  As an administrator user
  I want to monitor the volume of asset categories

  @acceptance-back @acceptance-front
  Scenario: Monitor the number of asset categories
    Given a catalog with 5 asset categories
    When the administrator user asks for the catalog volume monitoring report
    Then the report returns that the number of asset categories is 5