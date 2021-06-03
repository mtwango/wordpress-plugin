PHONY :=

PHONY += test
test: CLEAN := tests/public tests/vendor tests/composer.lock
test:
	rm -rf $(CLEAN)
	docker run --rm -it -v $(shell pwd):/app composer:2 --working-dir=tests install --no-interaction
	rm -rf $(CLEAN)

.PHONY: $(PHONY)
