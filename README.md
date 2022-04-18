## PHP Test Generator

Generates PHP unit tests

### Installation

1. Run `wget -O generate-php-test https://github.com/zenas1210/php-test-generator/raw/master/php-test-generator.phar`

The phar package can be stored anywhere as long as it's executed from the root directory of your application.

### Usage

Run `php generate-php-test App\TestedService`

`TestedServiceTest.php` will appear in tests directory resolved from composer.json, or `tests` if unable to resolve.
