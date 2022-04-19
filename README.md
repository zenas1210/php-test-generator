## PHP Test Generator

Generates PHP unit tests

### General requirements
* Use composer
* Use psr-4 autoloading

### Installation

`wget -O generate-php-test https://github.com/zenas1210/php-test-generator/raw/master/php-test-generator.phar`

The phar package can be stored anywhere as long as it's executed from the root directory of your application.

### Usage

`php generate-php-test Some\ClassName Another\ClassName`

Test classes will appear in tests directory resolved from composer.json, or `tests` if unable to resolve.

`--namespace|-s`

Specifies root namespace of application classes. By default, it's resolved from composer.json.

`--test-namespace|-t`

Specifies root namespace of test classes. By default, it's resolved from composer.json.

`--test-dir|-d`

Specifies root test directory. By default, it's resolved from composer.json.

`--overwrite|-f`

Overwrites existing test file/files (if any).

`--data-providers|-p`

Generates a @dataProvider for every method.
