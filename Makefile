include .docker/.env

DC = docker compose --project-directory=.docker --file=".docker/docker-compose.yaml"

.DEFAULT_GOAL      = help

.PHONY: help
help:
	@grep -E '(^[a-zA-Z0-9_-]+:.*?##.*$$)|(^##)' Makefile | awk 'BEGIN {FS = ":.*?## "}{printf "\033[32m%-30s\033[0m %s\n", $$1, $$2}' | sed -e 's/\[32m##/[33m/'

### DEV
.PHONY: app-build app-init up down
app-build: ## Build image
	$(DC) build

app-init: up ## Init application
	@$(DC) exec php composer install
	@$(DC) exec go bash -c "cd goclient && go build"
	@$(MAKE) init-kafka-topic

.PHONY: init-kafka-topic
init-kafka-topic:
	@$(DC) exec kafka sh -c "\
		kafka-topics.sh --bootstrap-server $(KAFKA_SERVER) \
			--create --topic $(KAFKA_TOPIC) --if-not-exists --partitions 1 --replication-factor 1 \
		&& kafka-configs.sh --bootstrap-server $(KAFKA_SERVER) \
			--alter --topic $(KAFKA_TOPIC) --add-config retention.ms=-1 \
	"

up: ## Start the project docker containers
	@$(DC) up -d

down: ## Down the docker containers
	@$(DC) down --timeout 25

.PHONY: php-shell go-shell
php-shell: ## Run shell in php container
	@$(DC) exec -it -u appuser php bash

go-shell: ## Run shell in go container
	@$(DC) exec -it -u appuser go bash

### KAFKA NATIVE
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

### CLIENTS
.PHONY: php-consume php-produce go-consume go-produce
php-consume:
	@$(DC) exec php bash -c "/app/bin/console app:rdkafka-consume"

php-produce:
	@$(DC) exec php bash -c "/app/bin/console app:rdkafka-produce"

go-consume:
	@$(DC) exec go bash -c "cd goclient && ./goclient consume"

go-produce:
	@$(DC) exec go bash -c "cd goclient && ./goclient produce"

### TOPIC DEV TOOLS

.PHONY: reset-topic-offset
reset-topic-offset:
	@$(DC) exec kafka sh -c "\
		kafka-consumer-groups.sh --bootstrap-server $(KAFKA_SERVER) \
			--group test-consumer-1 --reset-offsets --to-earliest --topic $(KAFKA_TOPIC) -execute\
	"
