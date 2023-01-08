ARGS := $(wordlist 2,$(words $(MAKECMDGOALS)),$(MAKECMDGOALS))
$(eval $(RUN_ARGS):;@:)

.PHONY: help

help:
	@awk 'BEGIN {FS = ":.*##"; printf "\nUsage:\n  make \033[36m<target> <options>\033[0m\n\nTargets:\n"} /^[a-zA-Z_-]+:.*?##/ { printf "  \033[36m%-20s\033[0m %s\n", $$1, $$2 }' $(MAKEFILE_LIST)

phpstan: ## runs phpstan
	php vendor/bin/phpstan analyze --memory-limit=-1 ${ARGS}

php-cs-fix: ## runs php-cs-fixer fix
	cd vendor/bin
	php-cs-fixer fix --config=.php-cs-fixer.dist.php --verbose --show-progress=dots

%:
	@:
