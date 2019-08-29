<?php

namespace Rumur\Pimpled\View;

use InvalidArgumentException;
use Rumur\Pimpled\Support\Arr;
use Rumur\Pimpled\Container\Container;
use Rumur\Pimpled\Foundation\Application;
use Rumur\Pimpled\Contracts\Support\Arrayable;
use Rumur\Pimpled\Contracts\View\View as ViewContract;
use Rumur\Pimpled\Contracts\View\Factory as FactoryContract;

class Factory implements FactoryContract
{
    /** @var Application */
    protected $app;

    /**
     * The folder where to look for a
     * overwritten views
     *
     * @var string
     */
    protected $prefix = '';

    /**
     * Shared params that will be passed to Views
     *
     * @var array
     */
    protected $shared = [];

    /**
     * Factory constructor.
     *
     * @param Container $app
     */
    public function __construct(Container $app)
    {
        $this->app = $app;
    }

    /**
     * Determine if a given view exists.
     *
     * @param  string  $view
     * @return bool
     */
    public function exists($view): bool
    {
        $path = $this->nameToPath($view);

        return file_exists($this->locateTemplate($path));
    }

    /**
     * Get the first view that actually exists from the given list.
     *
     * @param  array  $views
     * @param  Arrayable|array   $data
     * @return ViewContract
     *
     * @throws InvalidArgumentException
     */
    public function first(array $views, $data = []): ViewContract
    {
        $view = Arr::first($views, function ($view) {
            return $this->exists($view);
        });

        if (! $view) {
            throw new InvalidArgumentException('None of the views in the given array exist.');
        }

        return $this->make($view, $data);
    }

    /**
     * @param string $view Dot notation $name e.g. `partials.home.title-item` the `.php` will be added automatically.
     * @param Arrayable|array $data
     *
     * @return ViewContract
     */
    public function make($view, $data = []): ViewContract
    {
        $path = $this->locateTemplate($this->nameToPath($view));

        return new View($this, $view, $path, $data);
    }

    /**
     * Shares params with all others Views instances
     * if they passed as a param.
     *
     * @param mixed $param
     * @param mixed $value
     *
     * @return $this
     */
    public function share($param, $value = null): self
    {
        if (is_array($param)) {
            $this->shared = array_merge($this->shared, $param);
        } elseif (is_string($param)) {
            $this->shared[$param] = $value;
        }

        return $this;
    }

    /**
     * Get an item from the shared data.
     *
     * @param  string  $key
     * @param  mixed   $default
     * @return mixed
     */
    public function shared($key, $default = null)
    {
        return Arr::get($this->shared, $key, $default);
    }

    /**
     * @return array
     */
    public function sharedData(): array
    {
        return $this->shared;
    }

    /**
     * Get the rendered content of the view based on a given condition.
     *
     * @param  bool  $condition
     * @param  string  $view
     * @param  Arrayable|array   $data
     * @return string
     */
    public function renderWhen($condition, $view, $data = []): string
    {
        if (! $condition) {
            return '';
        }

        return $this->make($view, $data)->render();
    }

    /**
     * @return Application
     */
    public function getContainer(): Application
    {
       return $this->app;
    }

    /**
     * Get the rendered content of the view if this view exists
     *
     * @param  string  $view
     * @param  Arrayable|array   $data
     * @return string
     */
    public function renderWhenExists($view, $data = []): string
    {
        return $this->renderWhen($this->exists($view), $view, $data);
    }

    /**
     * Sets the folder within the theme where to look for files
     * to overwrite with
     *
     * @param string $prefix
     *
     * @uses sanitize_title()
     *
     * @return $this
     */
    public function useDirWithinThemes($prefix): self
    {
        $this->prefix = \sanitize_title($prefix);

        return $this;
    }

    /**
     * @param string $path
     *
     * @uses trailingslashit()
     * @uses locate_template()
     *
     * @return string
     */
    protected function locateTemplate($path): string
    {
        $path = ltrim($path, DIRECTORY_SEPARATOR);

        $tpl_name_with_prefix = trailingslashit($this->prefix) . $path;

        $located_from_theme = locate_template($tpl_name_with_prefix);

        return $located_from_theme ?: $this->app->viewsPath($path);
    }

    /**
     * Makes the path from the string divided by "." or "/"
     *
     * @param string $name The name with dot notation
     *
     * @uses trailingslashit()
     *
     * @return string    Returns the path to the file.
     */
    protected function nameToPath($name): string
    {
        $delimiter = strpos(DIRECTORY_SEPARATOR, $name) === false ? '.' : DIRECTORY_SEPARATOR;

        $raw_data = explode($delimiter, $name);

        $file = rtrim(array_pop($raw_data), '.php') . '.php';

        $path = trailingslashit(implode(DIRECTORY_SEPARATOR, $raw_data));

        return $path . $file;
    }
}
