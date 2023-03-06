ARGS := $(wordlist 2,$(words $(MAKECMDGOALS)),$(MAKECMDGOALS))
$(eval $(RUN_ARGS):;@:)

.PHONY: help

help:
	@awk 'BEGIN {FS = ":.*##"; printf "\nUsage:\n  make \033[36m<target>\033[0m\n\nTargets:\n"} /^[a-zA-Z_-]+:.*?##/ { printf "  \033[36m%-20s\033[0m %s\n", $$1, $$2 }' $(MAKEFILE_LIST)

phpstan: ## runs phpstan
	bash -c 'vendor/bin/phpstan analyze --memory-limit=-1 --xdebug ${ARGS}'

phpstan-raw: ## runs phpstan with raw output
	bash -c 'vendor/bin/phpstan analyze --memory-limit=-1 --xdebug --error-format=raw ${ARGS}'

php-cs-fix: ## runs php-cs-fixer fix
	bash -c 'vendor/bin/php-cs-fixer fix -v ${ARGS}'

test: ## runs tests
	bash -c 'vendor/bin/phpunit ${ARGS}' || true
	bash -c 'mkdir -p var/coverage/_css'
	bash -c 'cp tests/assets/coverage.css var/coverage/_css/custom.css'

serve-coverage:
	bash -c 'php -S localhost:80 -t var/coverage'

test-and-serve: test serve-coverage

%:
	@:
