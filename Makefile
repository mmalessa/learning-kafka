include .docker/.env

DC = docker compose --project-directory=.docker --file=".docker/docker-compose.yaml"

.DEFAULT_GOAL      = help

.PHONY: help
help:
	@grep -E '(^[a-zA-Z0-9_-]+:.*?##.*$$)|(^##)' Makefile | awk 'BEGIN {FS = ":.*?## "}{printf "\033[32m%-30s\033[0m %s\n", $$1, $$2}' | sed -e 's/\[32m##/[33m/'

### DEV
.PHONY: build init up down shell
build: ## Build image
	$(DC) build

init: ## Init application
	@$(DC) exec php composer install
	@#$(DC) exec php bash -c "bin/console messenger:setup-transports"

up: ## Start the project docker containers
	@$(DC) up -d

down: ## Down the docker containers
	@$(DC) down --timeout 25

shell: ## Run shell in php container
	@$(DC) exec -it -u appuser php bash

consume: ## Run consumer
	@$(DC) exec -it -u appuser php /app/bin/console messenger:consume order_consumer

produce: ## Dispatch some message
	@$(DC) exec -it -u appuser php /app/bin/console app:dispatch-message

### SYMFONY
.PHONY: composer console
composer: ## Run composer in php container.
	$(DC) exec $(PHP_CONTAINER) composer $(filter-out $@,$(MAKECMDGOALS))
console: ## Run symfony console in php container.
	$(EXEC_PHP) bin/console $(filter-out $@,$(MAKECMDGOALS))

### KAFKA
.PHONY: topics topic topic-create producer-create consumer-groups consumer-group
topics: ## Display list of topics
	$(DC) exec kafka kafka-topics.sh --list --bootstrap-server $(KAFKA_SERVER)

topic: ## Describe existing topic
	$(DC) exec kafka kafka-topics.sh --describe --bootstrap-server $(KAFKA_SERVER) --topic $(filter-out $@,$(MAKECMDGOALS))

topic-create: ## Create new topic
	$(DC) exec kafka kafka-topics.sh --create --bootstrap-server $(KAFKA_SERVER) --topic $(filter-out $@,$(MAKECMDGOALS))

producer-create: ## Create a topic producer
	$(DC) exec kafka kafka-console-producer.sh --bootstrap-server $(KAFKA_SERVER) --topic $(filter-out $@,$(MAKECMDGOALS))

consumer-groups: ## Display list of consumer group
	$(DC) exec kafka kafka-consumer-groups.sh --list --bootstrap-server $(KAFKA_SERVER)

consumer-group: ## Describe existing consumer group
	$(DC) exec kafka kafka-consumer-groups.sh --describe --bootstrap-server $(KAFKA_SERVER) --group $(filter-out $@,$(MAKECMDGOALS))
