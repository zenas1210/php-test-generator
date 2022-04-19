<?php

declare(strict_types=1);

namespace Tests;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase as BaseKernelTestCase;

class KernelTestCase extends BaseKernelTestCase
{
    public static function assertFileEquals(string $expected, string $actual, string $message = '', bool $canonicalize = false, bool $ignoreCase = false): void
    {
        static::assertFileExists($expected, $message);
        static::assertFileExists($actual, $message);

        static::assertEquals(
            str_replace("\r\n", "\n", file_get_contents($expected)),
            file_get_contents($actual)
        );
    }
}
