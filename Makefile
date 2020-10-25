PHONY :=

PHONY += test
test:
	rm -rf test/public test/vendor test/composer.lock
	docker run --rm --interactive --tty \
      --volume $(shell pwd)/test:/app \
      --volume $(shell pwd):/mona-plugin \
      --volume ~/.composer:/tmp \
      composer:1.10.15 install --no-dev --no-suggest

PHONY += test-2
test-2:
	rm -rf test/public test/vendor test/composer.lock
	docker run --rm --interactive --tty \
      --volume $(shell pwd)/test:/app \
      --volume $(shell pwd):/mona-plugin \
      --volume ~/.composer:/tmp \
      composer:2.0 install --no-dev

.PHONY: $(PHONY)
