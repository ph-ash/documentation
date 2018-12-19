# Phash documentation

Documentation of the Phash Ecosystem

This repository is used as main entry point and documentation for the different projects of Phash.

* [ph-ash/board](https://github.com/ph-ash/board)
* [ph-ash/server](https://github.com/ph-ash/server)

## General Architecture

![General Architecture](architecture.png)

## Installation

TBD

    docker-compose up

## Configuration

TBD

## Usage

After successful installation the [board](https://github.com/ph-ash/board) is reachable via the [https://localhost/](http://localhost/) url in your Browser.
Simply type it into the address bar and hit _enter_. Now you see the login screen popping up:

![Login](assets/phash_usage_board_login.png)

Enter the credentials you configured in the [Installation](#installation) chapter.
With the correct credentials submitted you will be redirected to the board:

![Filled_board](assets/phash_usage_board_1.png)
 
 As you did not push any monitoring data yet, the board is empty.
 
 To fill the board you make `post` requests against the `http://localhost/api/monitoring/data` url.
 
 For Example: 
 
 ```
 curl -X POST "http://localhost/api/monitoring/data" 
 -H "accept: application/json" 
 -H "Authorization: Bearer YOUR-CONFIGURED-TOKEN" 
 -H "Content-Type: application/json" 
 -d "{ \"id\": \"My First Monitoringdata\", \"status\": \"ok\", \"payload\": \"This Monitoring is my payload!\", \"idleTimeoutInSeconds\": 60, \"priority\": 1, \"date\": \"2018-12-19T13:42:46.790Z\"}"
 ```

The Authorization Header consists of the Bearer type and your token which you configured previously in the [Installation](#installation) chapter.
The `Content-Type: application/json` header is mandatory to make the api work properly. 
As last part of your request you provide the actual monitoring data in json-format, which looks like:

```
{
  "id": "My First Monitoringdata",
  "status": "ok",
  "payload": "This is my payload!",
  "idleTimeoutInSeconds": 60,
  "priority": 1,
  "date": "2018-12-19T13:42:46.790Z"
}
```

* `id` is the identifier for the tile on the board and for storage in the MongoDB
* `status` defines in which color the tile will appear
    * _green_ for _ok_
    * _yellow_ for _idle_ 
    * _red_ for _error_
* `payload` is the message which will be displayed for the tile
* `idleTimeoutInSeconds` defines after how many seconds a _green_/_ok_ tile will change its status to _yellow_/_idle_
* `priority` defines the display size of the tile on the board, the higher the _priority_, the bigger the tile
* `date` defines when the monitoring data was created, from this date on `idleTimeoutInSeconds` is calculated

If you receive an empty Response with a HTTP code of 201, your monitoring data was successfully accepted by the [server](https://github.com/ph-ash/server)
and should be displayed on the board.
There is an api sandbox located under `https://localhost/api/doc` which you can use for testing. When opening the
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

Every time you reload the board, 
all stored monitorings will be resent from the server to the board, so you
do not have to push them again.

## Issues

All issues regarding any of the components should be tracked at the [documentation repository issue tracker](https://github.com/ph-ash/documentation/issues).

## Thanks

The visualization relies on the great [albertopereira/vuejs-treemap](https://github.com/albertopereira/vuejs-treemap) Vue component.
