.PHONY: up down install ci stan cs cs-fix test migrate config init feature cache-clear migrate-update

ifeq ($(CI),true)
DOCKER_COMPOSE_OPTIONS = -u 451:451 -T
else
DOCKER_COMPOSE_OPTIONS = -T
endif

up:
	docker compose up -d

down:
	docker compose down

install:
	docker compose exec $(DOCKER_COMPOSE_OPTIONS) php composer install

ci:
	docker compose exec $(DOCKER_COMPOSE_OPTIONS) php composer ci

stan:
	docker compose exec $(DOCKER_COMPOSE_OPTIONS) php composer phpstan

cs:
	docker compose exec $(DOCKER_COMPOSE_OPTIONS) php composer cs

test:
	docker compose exec $(DOCKER_COMPOSE_OPTIONS) php composer test

migrate:
	docker compose exec $(DOCKER_COMPOSE_OPTIONS) php ./bin/console orm:schema-tool:create

migrate-update:
	docker compose exec $(DOCKER_COMPOSE_OPTIONS) php ./bin/console orm:schema-tool:update --force

bash:
	docker compose exec php bash

config:
	sh ./docker/config.sh

cache-clear:
	docker compose exec $(DOCKER_COMPOSE_OPTIONS) php ./bin/console projectaanvraag:cache-clear

init: install migrate
