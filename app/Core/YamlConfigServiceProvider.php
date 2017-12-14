<?php

namespace CultuurNet\ProjectAanvraag\Core;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Symfony\Component\Yaml\Yaml;


class YamlConfigServiceProvider implements ServiceProviderInterface
{
    protected $file;

    public function __construct($file) {
        $this->file = $file;
    }

    public function register(Container $pimple) {
        $config = Yaml::parse(file_get_contents($this->file));

        if (is_array($config)) {
            $this->importSearch($config, $pimple);

            if (isset($pimple['config']) && is_array($pimple['config'])) {
                $pimple['config'] = array_replace_recursive($pimple['config'], $config);
            } else {
                $pimple['config'] = $config;
            }
        }

    }

    /**
     * Looks for import directives..
     *
     * @param array $config
     *   The result of Yaml::parse().
     */
    public function importSearch(&$config, $app) {
        foreach ($config as $key => $value) {
            if ($key == 'imports') {
                foreach ($value as $resource) {
                    $base_dir = str_replace(basename($this->file), '', $this->file);
                    $new_config = new YamlConfigServiceProvider($base_dir . $resource['resource']);
                    $new_config->register($app);
                }
                unset($config['imports']);
            }
        }
    }

    public function getConfigFile() {
        return $this->file;
    }
}

