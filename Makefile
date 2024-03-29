.PHONY: up down install ci stan cs cs-fix test migrate config init feature cache-clear migrate-update

up:
	docker-compose up -d

down:
	docker-compose down

install:
	docker exec -it php.projectaanvraag composer install

ci:
	docker exec -it php.projectaanvraag composer ci

stan:
	docker exec -it php.projectaanvraag composer phpstan

cs:
	docker exec -it php.projectaanvraag composer cs

test:
	docker exec -it php.projectaanvraag composer test

migrate:
	docker exec -it php.projectaanvraag ./bin/console orm:schema-tool:create

migrate-update:
	docker exec -it php.projectaanvraag ./bin/console orm:schema-tool:update --force

bash:
	docker exec -it php.projectaanvraag bash

config:
	sh ./docker/config.sh

cache-clear:
	docker exec -it php.projectaanvraag ./bin/console projectaanvraag:cache-clear

init: install migrate
