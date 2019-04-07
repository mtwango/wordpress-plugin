PHONY :=

PHONY += test
test:
	rm -rf test/vendor
	rm -rf test/composer.lock
	docker run --rm --interactive --tty \
      --volume $(shell pwd)/test:/app \
      --volume $(shell pwd):/mona-plugin \
      --volume ~/.composer:/tmp \
      composer:1.8.4 install --no-dev --no-suggest

.PHONY: $(PHONY)
