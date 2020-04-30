.PHONY: build test phpstan

build:
	@docker build -tapisimulatorbundlephp73 .

test:
	@docker run --rm -v "${PWD}:/app" -w '/app' apisimulatorbundlephp73 vendor/bin/phpunit tests/; \
	echo "\nCoverage here: file://${PWD}/build/coverage/index.html"

phpstan:
	@docker run --rm -v "${PWD}:/app" -w '/app' apisimulatorbundlephp73 vendor/bin/phpstan analyze src --level=5