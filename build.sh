#!/bin/bash
function copy_env {
  cp ./.env ./app/.env
}
echo 'Envirment preparing'
copy_env
echo 'Envirment ready'
echo 'Starting Docker build'
docker-compose build
echo "Docker builded "
echo 'Docker composing'
docker-compose up -d || exit
echo "Docker composed"
echo "Composer install and migrations"
WORKDIR=$(grep WORKDIR .env | cut -d '=' -f2)
PHP_HOST=$(grep PHP_HOST .env | cut -d '=' -f2)
docker exec $PHP_HOST composer install -d $WORKDIR
docker exec $PHP_HOST php $WORKDIR/bin/console doctrine:migrations:migrate
echo "Install and migrations done"
echo "Wait 10 seconds for Rabbitmq ready"
sleep 15
echo "Starting consumer"
CONSUMER_NAME=$(grep CONSUMER_NAME .env | cut -d '=' -f2)
docker exec $PHP_HOST php $WORKDIR/bin/console rabbitmq:setup-fabric
docker exec $PHP_HOST php $WORKDIR/bin/console rabbitmq:consumer $CONSUMER_NAME -vvv
echo "Consumer started"
