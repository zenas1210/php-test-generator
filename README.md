## PHP Test Generator

Generates PHP unit tests

1. Add this to your composer.json
```json
"repositories": [
    {
        "type": "vcs",
        "url": "https://github.com/zenas1210/php-test-generator"
    }
]
```

2. Run `composer require --dev zenas/php-test-generator dev-master`

So far it just assumes `src` as root directory of PHP classes and `App` as root namespace.

Usage example: `vendor/bin/generate-php-test App\TestedService`

`TestedServiceTest.php` will appear in `tests` directory
