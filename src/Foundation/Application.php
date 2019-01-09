<?php

namespace Pmld\Foundation;

use Closure;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

use Pmld\Support\Arr;
use Pmld\Support\Collection;
use Pmld\Support\ServiceProvider;
use Pmld\Contracts\Config\Repository as RepositoryContract;

class Application extends Container
{
    /**
     * The Application version.
     *
     * @var string
     */
    const VERSION = '1.0.0';

    /**
     * @var Application
     */
    protected static $_instance = null;

    /**
     * Points to the Plugin directory
     *
     * @since 0.0.1
     *
     * @var string
     */
    protected $basePath;

    /**
     * All of the registered service providers.
     *
     * @var array
     */
    protected $serviceProviders = [];

    /**
     * The names of the loaded service providers.
     *
     * @var array
     */
    protected $loadedProviders = [];

    /**
     * Indicates if the application has been bootstrapped or not.
     *
     * @var bool
     */
    protected $hasBeenBootstrapped = false;

    /**
     * Indicates if the application has booted.
     *
     * @var bool
     */
    protected $booted = false;

    /**
     * List of booting callbacks.
     *
     * @var array
     */
    protected $bootingCallbacks = [];

    /**
     * List of booted callbacks.
     *
     * @var array
     */
    protected $bootedCallbacks = [];

    /**
     * @var string
     */
    protected $namespace;

    /**
     * Application constructor.
     *
     * @param string $basePath of the included plugin file.
     * @param array $values The parameters or objects
     */
    public function __construct($basePath, array $values = array())
    {
        parent::__construct($values);

        if ($basePath) {
            $this->setBasePath($basePath);
        }

        $this->registerBaseBindings();

        $this->registerBaseServiceProviders();
    }

    /**
     * Gets the App version
     *
     * @return string
     */
    public function version()
    {
        return static::VERSION;
    }

    /**
     * Register the basic bindings into the container.
     *
     * @return void
     */
    protected function registerBaseBindings()
    {
        static::setInstance($this);

        $this->instance('app', $this);

        $this->instance(Container::class, $this);
    }

    /**
     * Register base service providers.
     */
    protected function registerBaseServiceProviders()
    {

    }

    /**
     * @param string $basePath
     *
     * @uses \plugin_dir_path()
     *
     * @return Application
     */
    protected function setBasePath($basePath)
    {
        $this->basePath = rtrim(\plugin_dir_path($basePath), '\/');

        $this->bindPathsInContainer();

        return $this;
    }

    /**
     * Get the base path of the plugin installation.
     *
     * @param string $path Optional path to append to the base path.
     *
     * @return string
     */
    public function basePath($path = '')
    {
        return $this->basePath.($path ? DIRECTORY_SEPARATOR.$path : $path);
    }

    /**
     * Bind all of the application paths in the container.
     */
    protected function bindPathsInContainer()
    {
        $this->instance('path', $this->basePath('app'));
        $this->instance('path.base', $this->basePath());
        $this->instance('path.lang', $this->langPath());
        $this->instance('path.config', $this->configPath());
        $this->instance('path.public', $this->publicPath());
        $this->instance('path.database', $this->databasePath());
        $this->instance('path.resources', $this->resourcesPath());
        $this->instance('path.uploads', $this->uploadsPath());
    }

    /**
     * @param Application $app
     */
    protected function setInstance(Application $app)
    {
        if (is_null(static::$_instance))
            static::$_instance = $app;
    }

    /**
     * @return Application|null
     */
    public static function getInstance()
    {
        return static::$_instance;
    }

    /**
     * @param $key
     * @param $value
     * @return mixed
     */
    public function instance($key, $value)
    {
        $this->offsetSet($key, $value);

        return $this->offsetGet($key);
    }

    /**
     * Get the path to the resources directory.
     *
     * @param  string  $path
     * @return string
     */
    public function resourcesPath($path = '')
    {
        return $this->basePath('resources').($path ? DIRECTORY_SEPARATOR.$path : $path);
    }

    /**
     * Get the database directory path.
     *
     * @param string $path
     *
     * @return string
     */
    public function databasePath($path = '')
    {
        return $this->basePath('database').($path ? DIRECTORY_SEPARATOR.$path : $path);
    }

    /**
     * Get the bootstrap directory path.
     *
     * @param string $path
     *
     * @return string
     */
    public function bootstrapPath($path = '')
    {
        return $this->basePath('bootstrap').($path ? DIRECTORY_SEPARATOR.$path : $path);
    }

    /**
     * Get the storage directory path.
     *
     * @param string $path
     *
     * @return string
     */
    public function storagePath($path = '')
    {
        return $this->basePath('storage').($path ? DIRECTORY_SEPARATOR.$path : $path);
    }

    /**
     * Get the path to the resources "lang" directory.
     *
     * @param string $path
     *
     * @return string
     */
    public function langPath($path = '')
    {
        return $this->resourcesPath('lang').($path ? DIRECTORY_SEPARATOR.$path : $path);
    }

    /**
     * Get the main application plugin configuration directory.
     *
     * @param string $path
     *
     * @return string
     */
    public function configPath($path = '')
    {
        return $this->basePath('config').($path ? DIRECTORY_SEPARATOR.$path : $path);
    }

    /**
     * Get the path of the plugin public path.
     *
     * @param string $path
     *
     * @return string
     */
    public function publicPath($path = '')
    {
        return $this->basePath('dist').($path ? DIRECTORY_SEPARATOR.$path : $path);
    }

    /**
     * Get the path of the uploads directory.
     *
     * @param string $path
     *
     * @uses \wp_upload_dir()
     *
     * @return string
     */
    public function uploadsPath($path = '')
    {
        return Arr::get(\wp_upload_dir(), 'basedir').($path ? DIRECTORY_SEPARATOR.$path : $path);
    }

    /**
     * Get the WordPress directory path.
     *
     * @param string $path
     *
     * @uses ABSPATH
     *
     * @return string
     */
    public function wordpressPath($path = '')
    {
        return ABSPATH.($path ? DIRECTORY_SEPARATOR.$path : $path);
    }

    /**
     * Get the WordPress theme directory path.
     *
     * @param string $path  path to the directory
     * @param string $type  Possible options 'inherit' | 'parent' | 'child' | 'all'
     *
     * @uses \get_template_directory()
     * @uses \get_stylesheet_directory()
     *
     * @return string | array       Array returns only if type is "all"
     */
    function themePath($path = '', $type = 'inherit')
    {
        $search_path = ($path ? DIRECTORY_SEPARATOR.$path : $path);

        $parent = \get_template_directory() . $search_path;
        $child  = \get_stylesheet_directory() . $search_path;

        switch ($type) {
            case 'inherit':
            case 'child':
                return $child;
            case 'parent':
                return $parent;
            default:
                // unique in case if there is only parent theme is available.
                $dirs = array_unique(['parent' => $parent, 'child' => $child]);

                return $dirs;
        }
    }

    /**
     * Gets the main plugin dirname
     *
     * @uses \plugin_basename
     */
    public function getDirName()
    {
        return basename($this->basePath());
    }

    /**
     * Determine if we are running in the console.
     *
     * @return bool
     */
    public function runningInConsole()
    {
        return php_sapi_name() == 'cli' || php_sapi_name() == 'phpdbg';
    }

    /**
     * Determine if application is in local environment.
     *
     * @return bool
     */
    public function isLocal()
    {
        return $this['env'] === 'local';
    }

    /**
     * Determine if application is in local environment.
     *
     * @return bool
     */
    public function isProduction()
    {
        return $this['env'] === 'production';
    }

    /**
     * Determine if application is in local environment.
     *
     * @return bool
     */
    public function isDevelopment()
    {
        return $this['env'] === 'development';
    }

    /**
     * Determine if the application is currently down for maintenance.
     *
     * @uses \wp_installing()
     *
     * @return bool
     */
    public function isDownForMaintenance()
    {
        $filePath = $this->wordpressPath('.maintenance');

        if (function_exists('wp_installing') && ! file_exists($filePath)) {
            return \wp_installing();
        }

        return file_exists($filePath);
    }

    /**
     * Register all of the configured providers.
     */
    public function registerConfiguredProviders()
    {
        $providers = $this['config']->get('app.providers');

        /**
         * Instantiate Service Providers.
         */
        array_walk($providers, function ($provider) {

            $this->register($instance = $this->resolveProvider($provider));

            if (! $this->booted) {
                $this->booting(function () use ($instance) {
                    $this->bootProvider($instance);
                });
            }
        });
    }

    /**
     * Boot the application's service providers.
     */
    public function boot()
    {
        if ($this->booted) {
            return;
        }

        /*
         * Once the application has booted we will also fire some "booted" callbacks
         * for any listeners that need to do work after this initial booting gets
         * finished. This is useful when ordering the boot-up processes we run.
         */
        $this->fireAppCallbacks($this->bootingCallbacks);

        array_walk($this->serviceProviders, function ($provider) {
            $this->bootProvider($provider);
        });

        $this->booted = true;

        $this->fireAppCallbacks($this->bootedCallbacks);
    }

    /**
     * Call the booting callbacks for the application.
     *
     * @param array $callbacks
     */
    protected function fireAppCallbacks(array $callbacks)
    {
        foreach ($callbacks as $callback) {
            call_user_func($callback, $this);
        }
    }

    /**
     * Boot the given service provider.
     *
     * @param ServiceProviderInterface $provider
     *
     * @return mixed
     */
    protected function bootProvider(ServiceProviderInterface $provider)
    {
        if (method_exists($provider, 'boot')) {
            return call_user_func([$provider, 'boot']);
        }
    }

    /**
     * Register a new boot listener.
     *
     * @param mixed $callback
     */
    public function booting($callback)
    {
        $this->bootingCallbacks[] = $callback;
    }

    /**
     * Register a new "booted" listener.
     *
     * @param mixed $callback
     */
    public function booted($callback)
    {
        $this->bootedCallbacks[] = $callback;

        if ($this->isBooted()) {
            $this->fireAppCallbacks([$callback]);
        }
    }

    /**
     * Verify if the application has been bootstrapped before.
     *
     * @return bool
     */
    public function hasBeenBootstrapped()
    {
        return $this->hasBeenBootstrapped;
    }

    /**
     * Bootstrap the application with given list of bootstrap
     * classes.
     *
     * @param array $bootstrappers
     *
     * @uses \do_action()
     */
    public function bootstrapWith(array $bootstrappers)
    {
        $this->hasBeenBootstrapped = true;

        foreach ($bootstrappers as $bootstrapper) {

            do_action("pmld.bootstrapping.{$bootstrapper}", $this);

            /*
             * Instantiate each bootstrap class and call its "bootstrap" method
             * with the Application as a parameter.
             */
            $this->instance($bootstrapper, function() use ($bootstrapper) {
                return new $bootstrapper($this);
            })->bootstrap($this);

            do_action("pmld.bootstrapped.{$bootstrapper}", $this);
        }
    }

    /**
     * Register a service provider with the application.
     *
     * @param ServiceProviderInterface $provider
     * @param array                    $values
     *
     * @return ServiceProviderInterface
     */
    public function register(ServiceProviderInterface $provider, array $values = [])
    {
        parent::register($provider, $values);

        $this->markAsRegistered($provider);

        // If the application has already booted, we will call this boot method on
        // the provider class so it has an opportunity to do its boot logic and
        // will be ready for any usage by this developer's application logic.
        if ($this->booted) {
            $this->bootProvider($provider);
        }

        return $provider;
    }

    /**
     * Get the service providers that have been loaded.
     *
     * @return array
     */
    public function getLoadedProviders()
    {
        return $this->loadedProviders;
    }

    /**
     * Resolve a service provider instance from the class name.
     *
     * @param string $provider
     *
     * @return ServiceProvider
     */
    public function resolveProvider($provider)
    {
        return class_exists($provider)
            ? new $provider($this)
            : null;
    }

    /**
     * Mark the given provider as registered.
     *
     * @param ServiceProviderInterface $provider
     */
    protected function markAsRegistered(ServiceProviderInterface $provider)
    {
        $this->serviceProviders[] = $provider;
        $this->loadedProviders[get_class($provider)] = true;
    }

    /**
     * Determine if the application is running unit tests.
     *
     * @return bool
     */
    public function runningUnitTests()
    {
        return $this['env'] == 'testing';
    }

    /**
     * Determine if the application has booted.
     *
     * @return bool
     */
    public function isBooted()
    {
        return $this->booted;
    }

    /**
     * Load configuration files based on given path.
     *
     * @param RepositoryContract $repository
     * @param string     $path                  The configuration files folder path.
     *
     * @return Application
     */
    public function loadConfigurationFiles(RepositoryContract $repository, $path = '')
    {
        $files = $this->getConfigurationFiles($path);

        // Load Configs once only.
        foreach ($files as $filename) {
            $config = require_once $filename;

            // We can use arrays only.
            if (! is_array($config)) continue;

            $key = basename($filename, '.php');

            // Merge existed config file with new one.
            // It might happens when developer want's to overwrite
            // or expand config file from the child/parent theme.
            if ($exist = $repository->has($key)) {
                $existed = $repository->get($key, []);
                $repository->set($key, array_merge($existed, $config));
            } else {
                $repository->set($key, $config);
            }
        }

        return $this;
    }

    /**
     * Get all configuration files.
     *
     * @param string $path     Path to the folder to load from
     *
     * @return array
     */
    protected function getConfigurationFiles($path)
    {
        $files = is_dir($path)
            ? glob($path . DIRECTORY_SEPARATOR . '*.php')
            : [];

        ksort($files, SORT_NATURAL);

        return $files;
    }

    /**
     * Set the application locale.
     *
     * @param string $locale
     *
     * @uses \do_action()
     */
    public function setLocale($locale)
    {
        $this['config']->set('app.locale', $locale);
        $this['translator']->setLocale($locale);

        \do_action('pmld.locale_updated', $locale, $this);
    }

    /**
     * Get the application locale.
     *
     * @return string
     */
    public function getLocale()
    {
        return $this['config']->get('app.locale');
    }

    /**
     * Check if passed locale is current locale.
     *
     * @param string $locale
     *
     * @return bool
     */
    public function isLocale($locale)
    {
        return $this->getLocale() == $locale;
    }

    /**
     * Register a list of hookable instances.
     *
     * @param string $config
     */
    public function registerConfiguredHooks($config = '')
    {
        if (empty($config)) {
            $config = 'app.hooks';
        }

        $hooks = Collection::make($this['config']->get($config));

        (new HooksRepository($this))->load($hooks->all());
    }

    /**
     * Create and register a hook instance.
     *
     * @param string $hook
     */
    public function registerHook($hook)
    {
        // Build a "Hookable" instance.
        // Hookable instances must extend the "Hookable" class.
        $instance = new $hook($this);

        if (! method_exists($instance, 'register')) {
            return;
        }

        $instance->register($hook);
    }
}
