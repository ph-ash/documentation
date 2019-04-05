#!/bin/ash

CHROME=`getent hosts chrome | awk '{print $1}'`
echo "determined ip of chrome: $CHROME"
export BEHAT_PARAMS="{\"extensions\":{\"Behat\\\\MinkExtension\":{\"sessions\":{\"default\":{\"chrome\":{\"api_url\":\"http://$CHROME:9222\"}}}}}}"

SCENARIO=""
if [[ $# -eq 1 ]]; then
    SCENARIO=$1
fi

echo "testing scenario '${SCENARIO}', starting in a few seconds..."

sleep 5
vendor/bin/behat ${SCENARIO}
