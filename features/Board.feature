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
    Then I see 1 monitoring tile
    And I see the monitoring "single 1" as a "green" tile

    When I update a monitoring through the API:
      | property | value             |
      | id       | single 1          |
      | status   | error             |
      | payload  | a helpful message |
    Then I see 1 monitoring tile
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


  Scenario: push multiple monitoring data and delete monitoring data
    Given an empty dashboard
    When I add some monitorings through the API with errors:
      | id     | property | value                    |
      | bulk 1 | status   | ok                       |
      | bulk 2 | status   | ok                       |
      | bulk 2 | date     | 2019-01-01T01:02:03.456Z |
      | bulk 3 | status   | error                    |
      | bulk 4 | status   | error                    |
      | bulk 4 | path     | bulk 1                   |
      | bulk 5 | status   | ok                       |
    Then I see 4 monitoring tiles
    And I see the monitoring "bulk 1" as a "green" tile
    And I see the monitoring "bulk 2" as a "yellow" tile
    And I see the monitoring "bulk 3" as a "red" tile
    And I see the monitoring "bulk 5" as a "green" tile

    When I delete the monitoring "bulk 5" through the API
    Then I see 3 monitoring tiles

    When I delete the monitoring "bulk 3" in the dashboard
    Then I see 2 monitoring tiles


  Scenario: monitorings idle if no data is pushed regularly
    Given an empty dashboard
    When I add a monitoring through the API:
      | property             | value |
      | id                   | idle  |
      | status               | ok    |
      | idleTimeoutInSeconds | 5     |
    Then I see 1 monitoring tile
    And I see the monitoring "idle" as a "green" tile

    When I wait for 10 seconds
    Then I see the monitoring "idle" as a "yellow" tile

    When I update a monitoring through the API:
      | property             | value |
      | id                   | idle  |
      | status               | ok    |
      | idleTimeoutInSeconds | 5     |
    Then I see the monitoring "idle" as a "green" tile

    When I update a monitoring through the API:
      | property             | value |
      | id                   | idle  |
      | status               | error |
      | idleTimeoutInSeconds | 5     |
    Then I see the monitoring "idle" as a "red" tile

    When I wait for 10 seconds
    Then I see the monitoring "idle" as a "red" tile


  Scenario: navigate the dashboard and URL shortcuts
    Given an empty dashboard
    When I add some monitorings through the API:
      | id           | property | value                 |
      | navigation 1 | status   | ok                    |
      | navigation 1 | path     | top.middle 1.bottom 1 |
      | navigation 2 | status   | error                 |
      | navigation 2 | path     | top.middle 1.bottom 1 |
      | navigation 3 | status   | ok                    |
      | navigation 3 | path     | top.middle 1.bottom 2 |
      | navigation 4 | status   | ok                    |
      | navigation 4 | path     | top.middle 2.bottom   |
    Then I see 1 monitoring tile
    And I see the monitoring "top" as a "red" tile

    When I click on the monitoring "top"
    Then I see 2 monitoring tiles
    And I see the monitoring "top.middle 1" as a "red" tile
    And I see the monitoring "top.middle 2" as a "green" tile

    When I click on the monitoring "top.middle 2"
    Then I see 1 monitoring tile
    And I see the monitoring "top.middle 2.bottom" as a "green" tile

    When I click on the monitoring "top.middle 2.bottom"
    Then I see 1 monitoring tile
    And I see the monitoring "top.middle 2.bottom.navigation 4" as a "green" tile

    When I click on the breadcrumb 2 times
    Then I see 2 monitoring tiles
    And I see the monitoring "top.middle 1" as a "red" tile
    And I see the monitoring "top.middle 2" as a "green" tile

    When I click on the monitoring "top.middle 1"
    Then I see 2 monitoring tiles
    And I see the monitoring "top.middle 1.bottom 1" as a "red" tile
    And I see the monitoring "top.middle 1.bottom 2" as a "green" tile

    When I click on the monitoring "top.middle 1.bottom 1"
    Then I see 2 monitoring tiles
    And I see the monitoring "top.middle 1.bottom 1.navigation 1" as a "green" tile
    And I see the monitoring "top.middle 1.bottom 1.navigation 2" as a "red" tile

    When I navigate the browser to "top.middle 1.bottom 2"
    Then I see 1 monitoring tile
    And I see the monitoring "top.middle 1.bottom 2.navigation 3" as a "green" tile


  Scenario: push different prioritized monitoring data and growing error tiles
    Given an empty dashboard
    When I add some monitorings through the API:
      | id         | property                      | value |
      | priority 1 | status                        | error |
      | priority 1 | tileExpansionGrowthExpression | * 6   |
      | priority 2 | status                        | ok    |
      | priority 2 | priority                      | 2     |
    Then I see 2 monitoring tiles
    And I see the monitoring "priority 1" as a "red" tile
    And I see the monitoring "priority 2" as a "green" tile
    And the monitoring "priority 2" is about "2" times bigger than "priority 1"

    When I wait for 60 seconds
    Then the monitoring "priority 1" is about "3" times bigger than "priority 2"
