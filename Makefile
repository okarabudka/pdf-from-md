export UID := $(shell id -u)
export GID := $(shell id -g)
RUN := docker compose run --rm app

.PHONY: install test stan pdf

install: ## Build the image and install dependencies
	docker compose build
	$(RUN) composer install --no-interaction

test: ## Run the tests
	$(RUN) composer test

stan: ## Static analysis (PHPStan, level max)
	$(RUN) composer stan

pdf: ## Build a PDF: make pdf DIR=docs/book [CHAPTERS=1] [OUT=book.pdf]
	@test -n "$(DIR)" || { echo "Usage: make pdf DIR=<folder inside the project> [CHAPTERS=1] [OUT=book.pdf]"; exit 1; }
	$(RUN) php bin/export.php -p "$(DIR)" $(if $(CHAPTERS),-c) -o "$(or $(OUT),book.pdf)"
