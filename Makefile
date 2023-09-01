.PHONY: up down install ci stan cs cs-fix test migrate config init feature

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
	docker exec -it php.projectaanvraag ./vendor/bin/doctrine-dbal migrations:migrate --no-interaction

bash:
	docker exec -it php.projectaanvraag bash

config:
	sh ./docker/config.sh

init: install migrate
