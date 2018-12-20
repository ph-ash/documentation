# Phash documentation

PHP monitoring dashboard documentation

## What is Phash?

Phash is a set applications to provide a PHP based monitoring dashboard, monitoring data gathering and distribution.

* [ph-ash/board](https://github.com/ph-ash/board)

    frontend server, provides the actual dashboard to browsers

* [ph-ash/nginx](https://github.com/ph-ash/nginx)

    custom configured nginx to serve the complete application

* [ph-ash/server](https://github.com/ph-ash/server)

    backend server, provides the public API for data pushers and a persistence layer

The inspiration for Phash were projects like [Smashing](https://github.com/Smashing/smashing) on the one hand and custom coded data collectors on the other hand.
The vision of this project is to provide an out-of-the box working, highly customisable and extensible monitoring framework to gather and display monitoring data.

### What it does

* display monitoring data on a screener board to quickly identify issues
* provide a central monitoring data endpoint
* store a current snapshot of monitoring data

### What it doesn't

* store a time series of monitoring data
* generate monitoring data reports

### What it should do soon

* periodically pull data from monitorable system like databases
* provide an event bus to further distribute monitoring data to external applications
* provide alerting mechanisms based on monitoring data

## General Architecture

![General Architecture](architecture.png)

## Installation

You can simply download the [docker-compose.yaml](https://raw.githubusercontent.com/ph-ash/documentation/master/docker-compose.yaml) and run

    docker-compose up

to start a local setup listening on [http://localhost](http://localhost).

If you want to destroy the setup, be sure to include the `-v` flag to also remove the named volumes:

    docker-compose down -v

## Configuration

You can configure the external ports of the application by changing the `ports` of the `nginx` service accordingly.

You can and should configure several application variables by adding `environment` variables the `docker-compose.yaml`. All variables are optional; the default settings are suitable for a local test setup.

### server
* `API_TOKEN` (default value: `pleaseChooseASecretTokenForThePublicAPI`)

    This token must be used by the data pushers as Bearer authorization header (see [Usage](#usage)). It is **highly recommended** to change this value for production systems.

* `MONGODB_URL` (default value: `mongodb://mongo:27017`)

    Complete URL of the MongoDB which stores submitted monitorings, in case you want to reuse an existing installation.

* `MONGODB_DB` (default value: `phash`)

    MongoDB name for storing submitted monitorings, in case you want to reuse an existing installation.

* `WAMP_HOST` (default value: `board`)

    The host name of the WAMP server.

* `WAMP_REALM` (default value: `realm1`)

    The WAMP realm to use for communication. Needs to match the [board setting](#board).

### board

* `BOARD_LOGIN_PASSWORD` (default value: `phash-board`)

    The password of the frontend user named `phash-board`. It is **highly recommended** to change this value for production systems.

* `PUBLIC_HOSTNAME` (default value: `localhost`)

    The public host name the board will be served under, necessary for the internal WAMP connection. It is *necessary* to change this value for production systems
    if you want to run this on a dedicated server.

* `WAMP_PASSWORD` (default value: `pleaseChooseASecretPasswordForTheWebsocket`)

    WAMP password for connections from browsers. It is **highly recommended** to change this value for production systems.

* `WAMP_REALM` (default value: `realm1`)

    The WAMP realm to use for communication. Needs to match the [server setting](#server).

## Usage

After successful installation the [board](https://github.com/ph-ash/board) is reachable via [https://localhost/](http://localhost/) in your preferred browser.
Simply type it into the address bar and hit _enter_. Now you see the login screen popping up:

![Login](assets/phash_usage_board_login.png)

Enter the credentials you configured in the [Configuration](#configuration) chapter (username: `phash-board`).
With the correct credentials submitted you will be redirected to the board:

![Filled_board](assets/phash_usage_board_1.png)

As you did not push any monitoring data yet, the board is empty.

To fill the board you make `POST` requests against [http://localhost/api/monitoring/data](http://localhost/api/monitoring/data).

For Example:

    curl -sS -D - -X POST "http://localhost/api/monitoring/data" \
    -H "accept: application/json" \
    -H "Authorization: Bearer YOUR-CONFIGURED-TOKEN" \
    -H "Content-Type: application/json" \
    -d "{ \"id\": \"My First Monitoringdata\", \"status\": \"ok\", \"payload\": \"This Monitoring is my payload\", \"idleTimeoutInSeconds\": 60, \"priority\": 1, \"date\": \"2018-12-19T13:42:46.790Z\"}"

The Authorization Header consists of the Bearer type and your token which you configured previously in the [Configuration](#configuration) chapter.
The `Content-Type: application/json` header is mandatory to make the API work properly.
As last part of your request you provide the actual monitoring data in JSON format, which looks like:

```
{
  "id": "My First Monitoringdata",
  "status": "ok",
  "payload": "This is my payload",
  "idleTimeoutInSeconds": 60,
  "priority": 1,
  "date": "2018-12-19T13:42:46.790Z"
}
```

* `id` is the identifier for the tile on the board and for storage in the MongoDB
* `status` defines in which color the tile will appear initially
    * _green_ for `ok`
    * _red_ for `error`
* `payload` is the message which will be displayed in the tile hover
* `idleTimeoutInSeconds` defines after how many seconds after `date` a _green_/`ok` tile will change its status to _yellow_/`idle` (_red_/`error` tiles are not affected). You should choose this value based on your data push interval.
* `priority` defines the display size of the tile on the board: the higher the _priority_, the bigger the tile in relation to other tiles
* `date` defines when the monitoring data was created. Serves as starting point of the `idle` calculation.

If you receive an empty Response with a HTTP code of `201`, your monitoring data was successfully accepted by the [server](https://github.com/ph-ash/server)
and should be displayed on the board.
Every time you reload the board,
all stored monitorings will be resent from the server to the board, so you
do not have to push them again.

### API Sandbox

There is an api sandbox located under [https://localhost/api/doc](https://localhost/api/doc) which you can use for testing. When opening the
url you will see the api documentation:

![api_sandbox_documentation](assets/phash_usage_api_doc_1.png)

Authorize yourself by clicking the _Authorize_ Button in the upper right corner.
Simply fill in the Bearer type with your configured token which should look like:

![api_sandbox_authorization](assets/phash_usage_api_doc_2.png)

Next open the Monitoring tab and click on _Try it out_ to switch to the interactive
sandbox. Here you may edit the data you want to send and execute the request.

![api_sandbox_authorization](assets/phash_usage_api_doc_4.png)

After submitting a few monitorings look at your board, you will see your posted monitorings as tiles with
different colors which represent the statuses mentioned earlier.

![api_sandbox_authorization](assets/phash_usage_board_2.png)

## Issues

All issues regarding any of the components should be tracked at the [documentation repository issue tracker](https://github.com/ph-ash/documentation/issues).

However, if you are sure to know which single component is affected, you may also track issues in the [appropriate repositories](#phash-documentation).

## Thanks

The visualization relies on the great [albertopereira/vuejs-treemap](https://github.com/albertopereira/vuejs-treemap) Vue component.

The loading spinner was created on [loading.io](https://loading.io/spinner/double-ring).
