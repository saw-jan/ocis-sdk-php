.PHONY: serve
serve: composer-install
	@cd test-client
	@php -S localhost:9000 -t test-client

.PHONY: composer-install
composer-install:
	@cd sdk	&& composer install
	@cd test-client && composer install
