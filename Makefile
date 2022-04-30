install:
	composer install

update:
	composer update

start:
	php -S localhost:8080 -t public public/index.php

validate:
	composer validate

du:
	composer dump-autoload
