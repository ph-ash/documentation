#!/bin/ash

MONGO_CONTAINER=`docker ps -q -f Name=phash_mongo`
docker exec ${MONGO_CONTAINER} mongo phash --eval "db.dropDatabase();"
while :; do sleep 5; done
