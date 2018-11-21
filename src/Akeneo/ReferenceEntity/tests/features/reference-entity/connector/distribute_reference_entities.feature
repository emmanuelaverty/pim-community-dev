Feature: Connection to e-commerce platforms and marketplaces
  In order to administrate the records of the reference entities enriched in the PIM, inside my e-commerce platform and marketplace backends
  As a connector
  I want to know the name of all the reference entities that are in the PIM

  @integration-back
  Scenario: Get a reference entity
    Given the Brand reference entity
    When the connector requests the Brand reference entity
    Then the PIM returns the Brand reference entity

  @integration-back
  Scenario: Notify an error when getting a non-existent reference entity
    Given some reference entities with some records
    When the connector requests a non-existent reference entity
    Then the PIM notifies the connector about an error indicating that the reference entity does not exist