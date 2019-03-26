#!/bin/bash

echo -e "\n`date '+[%Y-%m-%d %H:%M:%S]'` starting behat tests...\n"

TOTAL=0
RESULT=0
while read SCENARIO; do
    SCENARIO=${SCENARIO} docker-compose --log-level ERROR -p phash -f docker-compose.yaml -f docker-compose.test.yaml up --force-recreate --abort-on-container-exit --exit-code-from tests
    CODE=$?
    RESULT=$((RESULT + CODE))
    TOTAL=$((TOTAL + 1))
done << EOF
$(BEHAT_PARAMS="{\"extensions\":{\"Behat\\\\MinkExtension\":{\"sessions\":{\"default\":{\"chrome\":{\"api_url\":\"dummy\"}}}}}}" vendor/bin/behat --list-scenarios)
EOF

# shutting down last containers
SCENARIO= docker-compose --log-level ERROR -p phash -f docker-compose.yaml -f docker-compose.test.yaml down -v

echo -e "\n`date '+[%Y-%m-%d %H:%M:%S]'` finished ${TOTAL} behat tests, ${RESULT} tests failed\n"
exit ${RESULT}
