<?php

namespace Pmld\Foundation\Bootstrap;

use Exception;
use Pmld\Config\Repository;
use Pmld\Foundation\Application;
use Pmld\Contracts\Config\Repository as RepositoryContract;

class ConfigurationLoader
{
    /**
     * @param Application $app
     * @throws Exception
     */
    public function bootstrap(Application $app)
    {
        /*
         * Load configuration repository.
         */
        $config = $app->instance('config', function() {
            $items = [];

            return new Repository($items);
        });

        $this->loadConfigurationFiles($app, $config);

        /*
         * Let's set the application environment based on received
         * configuration.
         */
        $app['env'] = $config->get('app.env', 'production');

        /*
         * date_default_timezone_set is set by default to UTC by WordPress.
         */
        mb_internal_encoding($config->get('app.charset', 'UTF-8'));
    }

    /**
     * Load configuration items from all found config files.
     *
     * @param Application        $app
     * @param RepositoryContract $repository
     *
     * @uses \apply_filters()
     *
     * @throws Exception
     */
    protected function loadConfigurationFiles(Application $app, RepositoryContract $repository)
    {
        $plugin_dir_name = $app->getDirName();

        $possible_dirs = array_unique(
            array_filter(
                \apply_filters('pmld.available_config_directories', [
                    $app->configPath(), // Plugin itself
                    $app->themePath('config' . DIRECTORY_SEPARATOR . $plugin_dir_name, 'parent'), // Parent Theme
                    $app->themePath('config' . DIRECTORY_SEPARATOR . $plugin_dir_name, 'child'),  // Child Theme
                ], $app, $this),
            'is_dir')
        );

        foreach ($possible_dirs as $path) {
            $app->loadConfigurationFiles($repository, $path);
        }

        if (! $repository->has('app')) {
            throw new Exception('Unable to load the "app" configuration file.');
        }
    }
}
