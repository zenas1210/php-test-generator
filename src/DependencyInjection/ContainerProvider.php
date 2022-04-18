<?php

namespace Zenas\PHPTestGenerator\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class ContainerProvider
{
    /** @var Container|null */
    private static $container;

    public static function get(): Container
    {
        if (self::$container !== null) {
            return self::$container;
        }

        self::$container = new ContainerBuilder();

        $loader = new YamlFileLoader(self::$container, new FileLocator(__DIR__ . '/../../config'));
        $loader->load('services.yml');

        self::$container->compile();

        return self::$container;
    }
}
