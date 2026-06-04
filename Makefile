.PHONY: up down restart destroy install ci stan cs test migrate bash config init cache-clear migrate-update

ifeq ($(CI),true)
DOCKER_COMPOSE_OPTIONS = -u 451:451 -T
else
DOCKER_COMPOSE_OPTIONS = -T
endif

up:
	docker network inspect platform > /dev/null 2>&1 || docker network create platform
	docker compose up -d

down:
	docker compose down

restart: down up

destroy:
	docker compose down -v

install:
	docker compose exec $(DOCKER_COMPOSE_OPTIONS) projectaanvraag composer install

ci:
	docker compose exec $(DOCKER_COMPOSE_OPTIONS) projectaanvraag composer ci

stan:
	docker compose exec $(DOCKER_COMPOSE_OPTIONS) projectaanvraag composer phpstan

cs:
	docker compose exec $(DOCKER_COMPOSE_OPTIONS) projectaanvraag composer cs

test:
	docker compose exec $(DOCKER_COMPOSE_OPTIONS) projectaanvraag composer test

migrate:
	docker compose exec $(DOCKER_COMPOSE_OPTIONS) projectaanvraag ./bin/console orm:schema-tool:create

migrate-update:
	docker compose exec $(DOCKER_COMPOSE_OPTIONS) projectaanvraag ./bin/console orm:schema-tool:update --force

bash:
	docker compose exec $(DOCKER_COMPOSE_OPTIONS) projectaanvraag bash

config:
	sh ./docker/config.sh

cache-clear:
	docker compose exec $(DOCKER_COMPOSE_OPTIONS) projectaanvraag ./bin/console projectaanvraag:cache-clear

init: install migrate
