# Phash documentation

PHP monitoring dashboard documentation

## What is Phash?

Phash is a set of applications to provide a PHP based monitoring dashboard, monitoring data gathering and distribution.

* [ph-ash/board](https://github.com/ph-ash/board) [![Build Status](https://travis-ci.com/ph-ash/board.svg?branch=master)](https://travis-ci.com/ph-ash/board)

    frontend server, provides the actual dashboard to browsers

* [ph-ash/server](https://github.com/ph-ash/server) [![Build Status](https://travis-ci.com/ph-ash/server.svg?branch=master)](https://travis-ci.com/ph-ash/server)

    backend server, provides the public API for data pushers and a persistence layer

* [ph-ash/nginx](https://github.com/ph-ash/nginx) [![Build Status](https://travis-ci.com/ph-ash/nginx.svg?branch=master)](https://travis-ci.com/ph-ash/nginx)

    custom configured nginx to serve the complete application

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

![General Architecture](assets/architecture/architecture.png)

### Why mongoDB

We chose to use [mongoDB](https://www.mongodb.com/) as our primary data storage server, because we

* have a very flat data model, which fits a document store more than a relational database management system
* therefore do not need the power of SQL
* neither do we need a high "ACIDity"

The data is persisted in a subfolder `mongodata` of the folder where you placed your `docker-compose.yaml`. This enables seamless updates without the need to collect all monitoring data again. 

## Installation

You can simply download the [docker-compose.yaml](https://raw.githubusercontent.com/ph-ash/documentation/master/docker-compose.yaml) and run

    docker-compose up -d

to start a local setup listening on [http://localhost](http://localhost).

You may then take the [first steps](FIRST_STEPS.md) to get used to the board.

If you want to destroy the setup, be sure to include the `-v` flag to also remove the named volumes:

    docker-compose down -v

## Configuration

You can configure the external ports of the application by changing the `ports` of the `nginx` service accordingly.

You can and should configure several application variables by overwriting `environment` variables. All variables are optional; the default settings are suitable for a local test setup, but not for a production system.

You should not change the `docker-compose.yaml` to configure your specific environment, but choose another way of storing your environment separately, to not bet affected by future updates of the `docker-compose.yaml`. See the [docker-compose documentation](https://docs.docker.com/compose/environment-variables/) for more information, recommended are ["Pass environment variables to containers"](https://docs.docker.com/compose/environment-variables/#pass-environment-variables-to-containers) and ["The 'env_file' configuration option"](https://docs.docker.com/compose/environment-variables/#the-env_file-configuration-option).

### board

| variable | default value | description |
| --- | --- | --- |
| `APP_SECRET` | `73adb4285b84053d174db4b65ffa8ff4bfa24e1b` | A secret for generating CSRF tokens. It is **highly recommended** to change this value for production systems. |
| `BOARD_LOGIN_PASSWORD` | `phash-board` | The password of the frontend user named `phash-board`. It is **highly recommended** to change this value for production systems. |
| `PUBLIC_WEBSOCKET_URL` | `ws://localhost/websocket` | The public websocket url, necessary for the internal WAMP connection. It is **necessary** to change this value if you either intend to run it on a dedicated server or behind a HTTPS proxy.<br/> If you use a dedicated server, replace `localhost` with the server host name.<br/> If you use a HTTPS proxy, change the URL scheme from `ws` to `wss`. |
| `WAMP_INTERNAL_HOSTNAME` | `board` | The internal hostname the websocket will listen for trusted connections from the server. This should always match the service name used in the `docker-compose.yaml`. |
| `WAMP_PASSWORD` | `pleaseChooseASecretPasswordForTheWebsocket` | WAMP password for connections from browsers. It is **highly recommended** to change this value for production systems. |
| `WAMP_REALM` | `realm1` | The WAMP realm to use for communication. Needs to match the [server setting](#server). |

### server

| variable | default value | description |
| --- | --- | --- |
| `API_TOKEN` | `pleaseChooseASecretTokenForThePublicAPI` | This token must be used by the data pushers as Bearer authorization header (see [Usage](#usage)). It is **highly recommended** to change this value for production systems. |
| `MONGODB_URL` | `mongodb://mongo:27017` | Complete URL of the MongoDB which stores submitted monitorings, in case you want to reuse an existing installation. |
| `MONGODB_DB` | `phash` | MongoDB name for storing submitted monitorings, in case you want to reuse an existing installation. |
| `WAMP_HOST` | `board` | The host name of the WAMP server. |
| `WAMP_REALM` | `realm1` | The WAMP realm to use for communication. Needs to match the [board setting](#board). |

## Usage

### Push single data to the board

#### Method, endpoint and headers

Data collectors can `POST` data in JSON format to the board's endpoint `http://<PUBLIC_HOSTNAME>/api/monitoring/data`.

They need to authenticate themselves with a `Bearer` token sent in the `Authorization` header.

The `Content-Type` and `Accept` headers should be set to `application/json`.

#### Payload format

The payload needs to be a JSON object with a given set of keys:

| key | type | description |
| --- | --- | --- |
| `id` | string | is the identifier for the tile on the board |
| `status` | `enum` (`ok`, `error`) | defines in which color the tile will appear initially (green or red) |
| `payload` | `string` | is the message which will be shown in a modal if you click a tile, may contain HTML |
| `idleTimeoutInSeconds` | `integer` | defines after how many seconds after `date` `ok` tile will change its status to `idle` (`error` tiles are not affected). You should choose this value based on your data push interval. |
| `priority` | `integer` | defines the display size of the tile on the board: the higher the _priority_, the bigger the tile in relation to other tiles |
| `date` | `date` | defines when the monitoring data was created. Serves as starting point of the `idle` calculation. |
| `path` | `string` | defines a tree path to place the monitoring at, can be `null`. Tree paths are formatted like `rootName.branchName.leafName`, see [tree paths](#tree-layout) for more information. |
| `tileExpansionIntervalCount` | `integer` | optionally defines the number of times `idleTimeoutInSeconds` has to be elapsed after which an `error` tile will grow by `tileExpansionGrowthExpression`. Defaults to `1` if not provided. See [error tile growth](#error-tile-growth) for more information. |
| `tileExpansionGrowthExpression` | `string` | optionally defines an expression for the growth of `error` tiles. Allowed formats are `+ <positive integer>` and `* <positive integer>`. Defaults to `+ 1`if not provided. See [error tile growth](#error-tile-growth) for more information. |

Example payload:

    {
      "id": "My First Monitoringdata",
      "status": "ok",
      "payload": "This is an error, fix it here <a href=\"https://github.com/ph-ash\" target=\"_blank\">My internal System</a>",
      "idleTimeoutInSeconds": 60,
      "priority": 1,
      "date": "2018-12-19T13:42:46.790Z",
      "path": null
    }

A complete example request with [curl](https://curl.haxx.se/) looks like:

    curl -sS -D - -X POST "http://localhost/api/monitoring/data" \
    -H "Accept: application/json" \
    -H "Authorization: Bearer pleaseChooseASecretTokenForThePublicAPI" \
    -H "Content-Type: application/json" \
    -d "{ \"id\": \"My First Monitoringdata\", \"status\": \"ok\", \"payload\": \"This is an error, fix it here <a href=\\"https://github.com/ph-ash\\" target=\\"_blank\\">My internal System</a>\", \"idleTimeoutInSeconds\": 60, \"priority\": 1, \"date\": \"2018-12-19T13:42:46.790Z\", \"path\": null}"

If you receive an empty Response with a HTTP code of `201`, your monitoring data was successfully accepted by the server and should be displayed on the board.
Every time you reload the board, all stored monitorings will be resent from the server to the board, so you do not have to push them again.

### Push bulk data to the board

#### Method, endpoint and headers

Data collectors can `POST` data in JSON format to the board's endpoint `http://<PUBLIC_HOSTNAME>/api/monitoring/data/bulk`.

They need to authenticate themselves with a `Bearer` token sent in the `Authorization` header.

The `Content-Type` and `Accept` headers should be set to `application/json`.

#### Payload format (bulk)

The payload needs to be a JSON object with an array called `monitoringData` of objects with given keys:

See [Payload format](#payload-format)

Example payload:
``` 
    {
        "monitoringData": [
            {
                "id": "My first Monitoringdata",
                "status": "ok",
                "payload": "This is my payload",
                "idleTimeoutInSeconds": 60,
                "priority": 1,
                "date": "2019-02-26T20:16:30.641Z",
                "path": "monitoring.team_phash.database"
            },
            {
                "id": "My second Monitoringdata",
                "status": "error",
                "payload": "This is an Errormessage",
                "idleTimeoutInSeconds": 60,
                "priority": 5,
                "date": "2019-02-26T20:16:30.641Z",
                "path": "monitoring.team_phash.database"
            }
        ]
    }
```

A complete example request with [curl](https://curl.haxx.se/) looks like:

    curl -sS -D - -X POST "http://localhost/api/monitoring/data/bulk" \
    -H "Accept: application/json" \
    -H "Authorization: Bearer pleaseChooseASecretTokenForThePublicAPI" \
    -H "Content-Type: application/json" \
    -d "{ \"monitoringData\": [ { \"id\": \"My first Monitoringdata\", \"status\": \"ok\", \"payload\": \"This is my payload\", \"idleTimeoutInSeconds\": 60, \"priority\": 1, \"date\": \"2019-02-26T20:16:30.641Z\", \"path\": \"monitoring.team_phash.database\" }, { \"id\": \"My second Monitoringdata\", \"status\": \"error\", \"payload\": \"This is an Errormessage\", \"idleTimeoutInSeconds\": 60, \"priority\": 5, \"date\": \"2019-02-26T20:16:30.641Z\", \"path\": \"monitoring.team_phash.database\" } ] }"

If you receive an empty Response with a HTTP code of `201`, your monitoring data was successfully accepted by the server and should be displayed on the board.
Every time you reload the board, all stored monitorings will be resent from the server to the board, so you do not have to push them again.

If you receive a Response with a HTTP code of `400` you will get a list of validation errors in the Response-Content. All 
data without errors will be processed successfully, while the ones with validation errors will not be processed until you resend 
them and they pass validation. 

### Delete monitoring data

#### Method, endpoint and headers

Data collectors can `DELETE` data from the server by calling the `http://<PUBLIC_HOSTNAME>/api/monitoring/{id}` endpoint.

They need to authenticate themselves with a `Bearer` token sent in the `Authorization` header.

The `Content-Type` and `Accept` headers should be set to `application/json`.

#### URL format

| key | type | description |
| --- | --- | --- |
| `id` | string | is the identifier for the tile on the board |

A complete example request with [curl](https://curl.haxx.se/) looks like:

    curl -sS -D - -X DELETE "http://localhost/api/monitoring/My%20first%20Monitoringdata" \
    -H "Accept: application/json" \
    -H "Authorization: Bearer pleaseChooseASecretTokenForThePublicAPI" \
    -H "Content-Type: application/json"
    
If you receive an empty Response with a HTTP code of `204`, your monitoring data has been successfully deleted by the server and should not be displayed on the board again.


### Tree layout

Your monitoring data can be displayed as a tree map. To enable this feature, you need to push the optional `path` property and fill it with a valid tree path.

A tree path is formatted like `rootName.branchName.leafName`. Example valid tree paths would be:
* `it.database.mssql`
* `it.database.mysql`
* `it.business.customers.registrations`

If you would push monitorings with all of the above tree paths, the resulting tree would look like (the `*` marks actual monitorings):

    |- it
       |- database
       |  |- mssql *
       |  |- mysql *
       |
       |- business
          |- customers
             |- registrations *

The tree map display allows you to easily navigate this tree by simply clicking in the node to descend and click on the breadcrumb on the top to ascend the tree.

Additionally, you will notice the URL changing when you navigate the tree. You can use these URLs to directly jump into that subtree when initially opening the dashboard, which can be useful for screeners.

Please keep in mind, that you should *not* declare a tree path both as branch node and leaf node. Pushing data into a branch node will result in validation errors and will not be processed.

### Error tile growth

From our experience, low prioritized monitorings usually won't be fixed unless there is nothing else to do. And let's be honest, this does not happen very often.

Every `error`tile will grow over time in priority (and on screen), so that it can compete with high priority monitorings. Each `error` tile will grow each

    <tileExpansionIntervalCount> * <idleTimeoutInSeconds> seconds

by
    
    <current priority> <tileExpansionGrowthExpression>
    
and will reset to the original priority as soon as the tile enters the status `ok`.

Examples:
* you pushed a monitoring with `idleTimeoutInSeconds = 60` and omitted the optional values: your error tile will grow each `60` seconds by `1`
* you pushed a monitoring with `idleTimeoutInSeconds = 120`, `tileExpansionIntervalCount = 2` and `tileExpansionGrowthExpression = '* 3'`: your error tile will `triple` its size each `240` seconds

## Issues

All issues regarding any of the components should be tracked at the [documentation repository issue tracker](https://github.com/ph-ash/documentation/issues).

However, if you are sure to know which single component is affected, you may also track issues in the [appropriate repositories](#phash-documentation).

## Thanks

The visualization relies on the great [albertopereira/vuejs-treemap](https://github.com/albertopereira/vuejs-treemap) Vue component.

The loading spinner was created on [loading.io](https://loading.io/spinner/double-ring).
