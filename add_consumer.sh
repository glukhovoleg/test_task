#!/bin/bash
echo "Starting consumer"
WORKDIR=$(grep WORKDIR .env | cut -d '=' -f2)
CONSUMER_NAME=$(grep CONSUMER_NAME .env | cut -d '=' -f2)
PHP_HOST=$(grep PHP_HOST .env | cut -d '=' -f2)
docker exec $PHP_HOST php $WORKDIR/bin/console rabbitmq:consumer $CONSUMER_NAME
echo "Consumer started"
