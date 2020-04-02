<?php

namespace CultuurNet\ProjectAanvraag;

use League\Container\Container;
use League\Container\ReflectionContainer;
use Noodlehaus\Config;

class ContainerFactory
{
    public static function forWeb(Config $config): Container
    {
        $container = self::build($config);
        return $container;
    }

    private static function build(Config $config): Container
    {
        $container = new Container();
        $container->delegate(new ReflectionContainer());
        $container->add(
            Config::class,
            $config
        );

        return $container;
    }
}
