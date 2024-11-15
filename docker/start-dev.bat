docker-compose -f ./docker-compose.yaml -f ./docker-compose.dev.yaml up -d
docker-compose -f ./docker-compose.yaml -f ./docker-compose.dev.yaml exec webserver php bin/console ca:cl
docker-compose -f ./docker-compose.yaml -f ./docker-compose.dev.yaml exec webserver php bin/console do:mi:mi
docker-compose -f ./docker-compose.yaml -f ./docker-compose.dev.yaml exec webserver php bin/console doctrine:fixtures:load
