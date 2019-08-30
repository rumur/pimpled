<?php

namespace Rumur\Pimpled\View;

use ArrayAccess;
use Rumur\Pimpled\Support\Traits\DigestArrayableData;
use RuntimeException;
use InvalidArgumentException;
use Rumur\Pimpled\Support\Arr;
use Rumur\Pimpled\Support\Collection;
use function Rumur\Pimpled\Support\value;
use Rumur\Pimpled\Contracts\Support\Arrayable;
use Rumur\Pimpled\Contracts\View\View as ViewContract;

class View implements ViewContract, ArrayAccess
{
    use DigestArrayableData;

    /**
     * The name of the view.
     *
     * @var string
     */
    protected $view;

    /**
     * @var Factory
     */
    protected $factory;

    /**
     * The template path.
     *
     * @var string
     */
    protected $path;

    /**
     * Params that would be passed to the template
     * as a variable.
     *
     * @var array
     */
    protected $data = [];

    /**
     * Share params that would be passed to all views
     * as a variable.
     *
     * @var array
     */
    protected $shared_data = [];

    /**
     * View constructor.
     * @param Factory $factory
     * @param string $view
     * @param string $path
     * @param mixed $data
     */
    public function __construct(Factory $factory, string $view, string $path, $data = [])
    {
        $this->path = $path;
        $this->view = $view;
        $this->factory = $factory;

        $this->data = $this->getArraybleData($data);
    }

    /**
     * Makes the HTML from the passed $name and $data
     *
     * @return string
     */
    public function content(): string
    {
        echo $this->render();
    }

    /**
     * @param string $name
     * @return $this
     */
    public function useName(string $name): self
    {
        $this->view = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function name(): string
    {
        return $this->view;
    }

    /**
     * @param $param
     * @return bool
     */
    public function hasParam($param): bool
    {
        return isset($this->params()[$param]);
    }

    /**
     * @param string $param
     * @param mixed|null $default
     * @return mixed|null
     */
    public function getParam(string $param, $default = null)
    {
        return $this->params()[$param] ?? $default;
    }

    /**
     * @hook pmld.view.{$this->view}_params
     *
     * @return array
     */
    public function params(): array
    {
        return apply_filters("pmld.view.{$this->view}_params", array_merge(
            $this->factory->sharedData(), $this->shared_data, $this->data), $this);
    }

    /**
     * @hook pmld.view.{$this->view}_compiled_params
     *
     * @return array
     */
    public function gatherData(): array
    {
        $params = Collection::make($this->data)->map(function($param) {
            if (!empty($this->shared_data) && $param instanceof ViewContract) {
                $param->share($this->shared_data);
            }
            return $param;
        })->toArray();

        return apply_filters("pmld.view.{$this->view}_compiled_params", array_merge(
            $this->factory->sharedData(), $this->shared_data, $params), $this);
    }

    /**
     * @return array
     */
    public function shared(): array
    {
        return $this->shared_data;
    }

    /**
     * Displays the content.
     *
     * @param callable|null $callback
     *
     * @return string
     */
    public function render(callable $callback = null)
    {
        try {

            $content = $this->resolve();

            $response = isset($callback) ? $callback($this, $content) : null;

            return $response ?? $content;

        } catch (\Throwable $e) {
            error_log($e->getMessage());
            return $this->factory->getContainer()->isDebug() ? $e->getMessage() : '';
        }
    }

    /**
     * @return false|string
     */
    protected function resolve()
    {
        $resolver = static function ($template, array $params = []) {

            $compiled = Collection::make($params)->map(static function ($param) {
                return value($param);
            })->toArray();

            extract($compiled, EXTR_SKIP);

            ob_start();
            include $template;
            return ob_get_clean();
        };

        if (file_exists($this->path)) {
            return $resolver($this->path, $this->gatherData());
        }

        throw new RuntimeException(sprintf('The <code>%s</code> was not found', $this->path));
    }

    /**
     * Shares params with all others Views instances
     * if they passed as a param.
     *
     * @param string|array $params
     * @param mixed $value
     * @throws InvalidArgumentException
     *
     * @return $this
     */
    public function share($params, $value = null): self
    {
        $this->shared_data = array_merge(
            $this->normalizeSharedParams($params, $value), $this->shared_data);

        return $this;
    }

    /**
     * @param string|array $params
     * @param mixed|null $value
     * @return array
     */
    protected function normalizeSharedParams($params, $value = null): array
    {
        $valid = false;

        if (is_array($params)) {
            $valid = true;
            // If it's not an associative array we assume that
            // these params should be taken from the current params state and be passed to a share ones.
            // e.g. ['user', 'form', 'content', ...]
            if (!Arr::isAssoc($params)) {
                $params = array_intersect_key($this->data, array_flip($params));
            }
        } elseif (is_string($params)) {
            $valid = true;
            // If there is no value and this param is presented within params array we assume that
            // these params should be taken from current params an passed to a share ones.
            if (null === $value && $this->hasParam($params)) {
                $params = [$params => $this->getParam($params)];
            } else {
                $params = [$params => $value];
            }
        }

        if ($valid) {
            return $params;
        }

        throw new InvalidArgumentException('The wrong <code>$params</code> passed, an array or a string was expected.');
    }

    /**
     * Adds vars to the tpl.
     *
     * @param mixed $params
     * @param mixed $value
     *
     * @return $this
     */
    public function with($params, $value = null): View
    {
        if (is_array($params)) {
            $this->data = array_merge($this->data, $params);
        } elseif (is_string($params)) {
            $this->data[$params] = $value;
        }

        return $this;
    }

    /**
     * @param Arrayable | array $errors
     * @return $this
     */
    public function withErrors($errors = []): View
    {
        return $this->with(['errors' => apply_filters("pmld.view.{$this->view}_with_errors",
            $this->getArraybleData($errors), $this)]);
    }

    /**
     * Determine if a piece of data is bound.
     *
     * @param  string  $key
     * @return bool
     */
    public function offsetExists($key): bool
    {
        return array_key_exists($key, $this->data);
    }

    /**
     * Get a piece of bound data to the view.
     *
     * @param  string  $key
     * @return mixed
     */
    public function offsetGet($key)
    {
        return $this->data[$key];
    }

    /**
     * Set a piece of data on the view.
     *
     * @param  string  $key
     * @param  mixed   $value
     * @return void
     */
    public function offsetSet($key, $value)
    {
        $this->with($key, $value);
    }

    /**
     * Unset a piece of data from the view.
     *
     * @param  string  $key
     * @return void
     */
    public function offsetUnset($key)
    {
        unset($this->data[$key]);
    }

    /**
     * Get a piece of data from the view.
     *
     * @param  string  $key
     * @return mixed
     */
    public function &__get($key)
    {
        return $this->data[$key];
    }

    /**
     * Set a piece of data on the view.
     *
     * @param  string  $key
     * @param  mixed   $value
     * @return void
     */
    public function __set($key, $value)
    {
        $this->with($key, $value);
    }

    /**
     * Check if a piece of data is bound to the view.
     *
     * @param  string  $key
     * @return bool
     */
    public function __isset($key)
    {
        return isset($this->data[$key]);
    }

    /**
     * Remove a piece of bound data from the view.
     *
     * @param  string  $key
     * @return void
     */
    public function __unset($key)
    {
        unset($this->data[$key]);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->render();
    }
}
