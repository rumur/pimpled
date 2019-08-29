<?php

namespace Pmld\App;

use Rumur\Pimpled\Support\Arr;
use Rumur\Pimpled\Foundation\Compat;
use Rumur\Pimpled\Foundation\Application;
use Rumur\Pimpled\Support\Facades\NoticeAdmin;

class Plugin extends Application
{
    /** @var string  */
    const NAME = 'Pimpled WP';

    /** @var string */
    const WP_VERSION_MIN = '4.7';

    /** @var string */
    const PHP_VERSION_MIN = '7.0';

    /** @var array Plugin Data provided by WP */
    protected $pluginData = [];

    /** @var string (__FILE__) of the plugin */
    protected $rootFile;

    /**
     * Whether the plugin is compatible or not.
     *
     * @var bool
     */
    private $isCompatible = true;

    /** @inheritdoc */
    public function __construct($basePath, array $values = [])
    {
        parent::__construct($basePath, $values);

        $this->rootFile = $basePath;
    }

    /**
     * @param string $path
     * @return bool|Application|null
     */
    public static function start($path = '')
    {
        // It's a Singleton
        if (static::getInstance() !== null) {
            return static::getInstance();
        }

        $app = new static($path);

        /**
         * List of bootstrap classes.
         *
         * @var array
         */
        $bootstrappers = \apply_filters('pmld.bootstrappers', [
            \Rumur\Pimpled\Foundation\Bootstrap\ConfigurationLoader::class,
            \Rumur\Pimpled\Foundation\Bootstrap\HandleExceptions::class,
            \Rumur\Pimpled\Foundation\Bootstrap\RegisterFacades::class,
            \Rumur\Pimpled\Foundation\Bootstrap\RegisterProviders::class,
            \Rumur\Pimpled\Foundation\Bootstrap\BootProviders::class,
        ], $app);

        if (! $app->hasBeenBootstrapped()) {
            $app->bootstrapWith($bootstrappers);
        }

        /**
         * Add WP_CLI's ServiceProviders.
         */
        if ($app->runningInConsole()) {
            $console_providers = array_unique(config('cli.providers', []));
            array_walk($console_providers, [$app, 'register']);
        }

        /**
         * Check for minimum PHP version.
         */
        if (! Compat::checkPHP($min_php = $app->pluginData( 'php_version_min', static::PHP_VERSION_MIN))) {
            // Add warning notice
            NoticeAdmin::warning(
                sprintf(
                    __('Minimal PHP version is required for %1$s plugin: <b>%2$s</b>.', PMLD_TD),
                    $app->pluginData('name', static::NAME), $min_php
                )
            );
            $app->isCompatible = false;
        }

        /**
         * Check for minimum WordPress version.
         */
        if (! Compat::checkWordPress($min_wp = $app->pluginData('wp_version_min', static::WP_VERSION_MIN))) {
            // Add warning notice
            NoticeAdmin::warning(
                sprintf(
                    __('Minimal WP version is required for %1$s plugin: <b>%2$s</b>.', PMLD_TD),
                    $app->pluginData('name', static::NAME), $min_wp
                )
            );
            $app->isCompatible = false;
        }

        /**
         * If there is no ignition with this env just do nothing.
         *
         * @since 0.0.1
         */
        if (! $app->isCompatible) {
            return false;
        }

        /**
         * Activation.
         *
         * @since 0.0.1
         */
        \register_activation_hook($app->basePath(), ['Pmld\\Activation', 'run']);

        /**
         * Uninstall.
         *
         * @since 0.0.1
         */
        \register_uninstall_hook($app->basePath(), ['Pmld\\Uninstall', 'run']);

        /**
         * Run admin hooks only.
         *
         * @since 0.0.1
         */
        if ( \is_admin() ) {
            $app['admin'] = function() use ($app) {
                \do_action('pmld.plugin.admin_start', $app);
            };
        }

        // Register application hooks.
        $app->registerConfiguredHooks();

        \do_action('pmld.plugin.started', $app);

        return $app;
    }

    /**
     * Gets the App version
     *
     * @return string
     */
    public function version()
    {
        try {
            return $this->pluginData('version', parent::version());
        } catch (\Exception $e) {
            return parent::version();
        }
    }

    /**
     * Gets the plugin data provided by WP
     *
     * @uses \get_file_data()
     *
     * @param string $key
     * @param mixed $default
     *
     * @return string|array
     */
    public function pluginData($key = null, $default = null)
    {
        if (empty($this->pluginData)) {
            $default_headers = [
                'name' => 'Plugin Name',
                'plugin_uri' => 'Plugin URI',
                'version' => 'Version',
                'description' => 'Description',
                'author' => 'Author',
                'author_uri' => 'Author URI',
                'text_domain' => 'Text Domain',
                'domain_path' => 'Domain Path',
                'network' => 'Network',
                'wp_version_min' => 'Requires at least',
                'php_version_min' => 'Requires PHP',
                'license' => 'License',
                'license_url' => 'License URI',
            ];
            $this->pluginData = \get_file_data($this->rootFile, $default_headers, 'plugin');
        }
        return $key !== null ? Arr::get($this->pluginData, $key, $default) : $this->pluginData;
    }

    /**
     * @param string $url
     *
     * @uses plugin_dir_url()
     *
     * @return string
     */
    public function getPublicUrl($url = '')
    {
        return \plugins_url($url, $this->rootFile);
    }
}
