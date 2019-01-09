<?php

namespace Pmld;

use Pmld\Support\Arr;
use Pmld\Admin\Admin;
use Pmld\Foundation\Compat;
use Pmld\Foundation\Application;
use Pmld\Support\Facades\NoticeAdmin;

class Plugin extends Application
{
    /** @var string  */
    const NAME = "Pimpled WP";

    /** @var string */
    const WP_VERSION_MIN = '4.7';

    /** @var string */
    const PHP_VERSION_MIN = '5.9';

    /** @var array Plugin Data provided by WP */
    protected $pluginData = [];

    /** @var string (__FILE__) of the plugin */
    protected $rootFile;

    /**
     * Whether the plugin is compatible or not.
     *
     * @var bool
     */
    private $is_compatible = true;

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
        if (! is_null(static::getInstance())) {
            return static::getInstance();
        }

        $app = new static($path);

        /**
         * List of bootstrap classes.
         *
         * @var array
         */
        $bootstrappers = \apply_filters('pmld.bootstrappers', [
            Foundation\Bootstrap\ConfigurationLoader::class,
            Foundation\Bootstrap\HandleExceptions::class,
            Foundation\Bootstrap\RegisterFacades::class,
            Foundation\Bootstrap\RegisterProviders::class,
            Foundation\Bootstrap\BootProviders::class,
        ], $app);

        if (! $app->hasBeenBootstrapped()) {
            $app->bootstrapWith($bootstrappers);
        }

        /**
         * Add WP_CLI's ServiceProviders.
         */
        if (defined('WP_CLI') && WP_CLI) {
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
                    Arr::get($app->pluginData(), 'name', static::NAME), $min_php
                )
            );
            $app->is_compatible = false;
        }

        /**
         * Check for minimum WordPress version.
         */
        if (! Compat::checkWordPress($min_wp = $app->pluginData('wp_version_min', static::WP_VERSION_MIN))) {
            // Add warning notice
            NoticeAdmin::warning(
                sprintf(
                    __('Minimal WP version is required for %1$s plugin: <b>%2$s</b>.', PMLD_TD),
                    Arr::get($app->pluginData(), 'name', static::NAME), $min_wp
                )
            );
            $app->is_compatible = false;
        }

        /**
         * If there is no ignition with this env just do nothing.
         *
         * @since 0.0.1
         */
        if (! $app->is_compatible) {
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
            $app['admin'] = function() {
                $admin = Admin::run();

                \do_action('pmld.plugin_admin_start', $admin);

                return $admin;
            };
        }

        // Register application hooks.
        $app->registerConfiguredHooks();

        \do_action('pmld.plugin_started', $app);

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
            $default_headers = array(
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
            );
            $this->pluginData = \get_file_data($this->rootFile, $default_headers, 'plugin');
        }

        return !is_null($key)
            ? Arr::get($this->pluginData, $key, $default)
            : $this->pluginData;
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

    /**
     * Determine if an application can use debug mode.
     *
     * @return bool
     */
    public function isDebug()
    {
        return $this->isLocal() || $this->isDevelopment();
    }
}
