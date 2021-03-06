# Changelog

## v2.1.0

* add version information
* add regression tests for the application
* fix delayed redrawing of idle monitorings

## v2.0.2

* fix to reject pushes into leafs
* fix resetting priority of grown tiles
* optimize update delay from API call to board push

## v2.0.1

* fix broken server docker image
* fix board update when bulk pushing
* optimized docker images build time and image size

## v2.0.0

* add detail view for monitorings
* add delete button in detail view
* add MonitoringData delete API
* add increasing monitoring tiles over time
* improved documentation
* fix documenation link to `docker-compose.yaml`
* add user feedback on websocket connection errors
* fix display error when path was `null`
* fix issues with empty boards
* fix error when a new board connected
* BC break: add persistence of mongoDB data to enable seamless upgrades
* BC break: remove separate port for websocket connections

## v1.1.0

* add Sub Tree URLs
* add MonitoringData Bulk Push API
* disable extensive logging
* reject pushes into branches
* fixed nginx healthcheck

## v1.0.1

* add possibility to host with SSL certificate

## v1.0.0

* add TreeMap View
* add API for MonitoringData pushes
