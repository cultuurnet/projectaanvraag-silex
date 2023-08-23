.PHONY: up down install ci stan cs cs-fix test migrate config init feature

up:
	docker-compose up -d

down:
	docker-compose down

install:
	docker exec -it projectaanvraag composer install

ci:
	docker exec -it projectaanvraag composer ci

stan:
	docker exec -it projectaanvraag composer phpstan

cs:
	docker exec -it projectaanvraag composer cs

test:
	docker exec -it projectaanvraag composer test

migrate:
	docker exec -it php.uitdatabank ./vendor/bin/doctrine-dbal migrations:migrate --no-interaction

bash:
	docker exec -it projectaanvraag bash

config:
	sh ./docker/config.sh

init: install migrate
