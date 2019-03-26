Feature: display monitoring state on a dashboard
  To have an overview of the current state and focus on incidents
  as a user
  I want to use a dashboard


  Background:
    Given I am logged into the dashboard


  Scenario: push single monitoring data and see details
    Given an empty dashboard
    When I add a monitoring through the API:
      | property | value    |
      | id       | single 1 |
      | status   | ok       |
    Then I see 1 monitoring tiles
    And I see the monitoring "single 1" as a "green" tile

    When I update a monitoring through the API:
      | property | value             |
      | id       | single 1          |
      | status   | error             |
      | payload  | a helpful message |
    Then I see 1 monitoring tiles
    And I see the monitoring "single 1" as a "red" tile

    When I add a monitoring through the API:
      | property | value             |
      | id       | single 2          |
      | status   | ok                |
    Then I see 2 monitoring tiles
    And I see the monitoring "single 1" as a "red" tile
    And I see the monitoring "single 2" as a "green" tile

    When I click on the monitoring "single 1"
    Then I see "a helpful message" in the detail view

#  Scenario: push multiple monitoring data and delete monitoring data (frontend + api)
#  Scenario: idletimeout
#  Scenario: filled board is navigated up and down (path + color aggregation!) + URL navigation
#  Scenario: tile expansion (incl. priority)
