<?php declare(strict_types=1);

namespace CultuurNet\ProjectAanvraag\Config;

use Noodlehaus\Config;
use Noodlehaus\Parser\Yaml;

final class ConfigFactory
{
    public static function create(string $configDir) : Config
    {
        return Config::load($configDir . '/config.yml', new Yaml());
    }
}
