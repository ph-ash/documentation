# First steps

After successful installation the board is reachable via [https://localhost/](http://localhost/) in your preferred browser.
Simply type it into the address bar and hit _enter_. Now you see the login screen popping up:

![Login](assets/usage/phash_usage_board_login.png)

Enter the credentials you configured in the [Configuration](README.md#configuration) chapter (username: `phash-board`).
With the correct credentials submitted you will be redirected to the board:

![Filled_board](assets/usage/phash_usage_board_1.png)

As you did not push any monitoring data yet, the board is empty.

To fill the board you make `POST` requests against [http://localhost/api/monitoring/data](http://localhost/api/monitoring/data), either directly or using our [API sandbox](#api-sandbox)-

For Example:

    curl -sS -D - -X POST "http://localhost/api/monitoring/data" \
    -H "accept: application/json" \
    -H "Authorization: Bearer pleaseChooseASecretTokenForThePublicAPI" \
    -H "Content-Type: application/json" \
    -d "{ \"id\": \"My First Monitoringdata\", \"status\": \"ok\", \"payload\": \"This Monitoring is my payload\", \"idleTimeoutInSeconds\": 60, \"priority\": 1, \"date\": \"2018-12-19T13:42:46.790Z\"}"

The Authorization Header consists of the Bearer type and your token which you configured previously in the [Configuration](README.md#configuration) chapter.
The `Content-Type: application/json` header is mandatory to make the API work properly.
As last part of your request you provide the actual monitoring data in JSON format.

## API Sandbox

There is an api sandbox located under [https://localhost/api/doc](https://localhost/api/doc) which you can use for testing. When opening the
url you will see the api documentation:

![api_sandbox_documentation](assets/usage/phash_usage_api_doc_1.png)

Authorize yourself by clicking the _Authorize_ Button in the upper right corner.
Simply fill in the Bearer type with your configured token (default: `pleaseChooseASecretTokenForThePublicAPI`) which should look like:

![api_sandbox_authorization](assets/usage/phash_usage_api_doc_2.png)

Next open the Monitoring tab and click on _Try it out_ to switch to the interactive
sandbox. Here you may edit the data you want to send and execute the request.

![api_sandbox_authorization](assets/usage/phash_usage_api_doc_3.png)

After submitting a few monitorings look at your board, you will see your posted monitorings as tiles with
different colors which represent the statuses mentioned earlier.

![api_sandbox_authorization](assets/usage/phash_usage_board_2.png)
