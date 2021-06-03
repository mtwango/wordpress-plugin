PHONY :=

PHONY += test-1
test-1:
	rm -rf tests/local/public tests/local/vendor tests/local/composer.lock
	docker run --rm --interactive --tty \
      --volume $(shell pwd)/tests/local:/app \
      --volume $(shell pwd):/mona-plugin \
      --volume ~/.composer:/tmp \
      composer:1 install --no-dev --no-suggest

PHONY += test-2
test-2:
	rm -rf tests/local/public tests/local/vendor tests/local/composer.lock
	docker run --rm --interactive --tty \
      --volume $(shell pwd)/tests/local:/app \
      --volume $(shell pwd):/mona-plugin \
      --volume ~/.composer:/tmp \
      composer:2 install --no-dev

.PHONY: $(PHONY)
