<?php

namespace Statamic\Tags;

use ArrayIterator;
use Statamic\Support\Str;
use Traversable;

class FluentTag implements \IteratorAggregate, \ArrayAccess
{
    /**
     * @var mixed
     */
    protected $name;

    /**
     * @var array
     */
    protected $context = [];

    /**
     * @var array
     */
    protected $params = [];

    /**
     * @var Loader
     */
    private $loader;

    /**
     * @var mixed
     */
    protected $fetched;

    /**
     * Instantiate fluent tag helper.
     *
     * @param  Loader  $loader
     */
    public function __construct(Loader $loader)
    {
        $this->loader = $loader;
    }

    /**
     * Invoke the class as a function.
     *
     * @param  string  $name
     * @return \Statamic\Tags\FluentTag
     */
    public function __invoke($name)
    {
        return static::make($name);
    }

    /**
     * Specify a tag name to start the tag param chain.
     *
     * @param  string  $name
     * @return \Statamic\Tags\FluentTag
     */
    public static function make($name)
    {
        $instance = app(self::class);

        $instance->name = $name;

        return $instance;
    }

    /**
     * Set the context.
     *
     * @param  array  $context
     * @return $this
     */
    public function context($context)
    {
        $this->context = $context;

        return $this;
    }

    /**
     * Fetch result of a tag.
     *
     * @return mixed
     */
    public function fetch()
    {
        if ($this->fetched) {
            return $this->fetched;
        }

        $name = $this->name;

        if ($pos = strpos($name, ':')) {
            $originalMethod = substr($name, $pos + 1);
            $method = Str::camel($originalMethod);
            $name = substr($name, 0, $pos);
        } else {
            $method = $originalMethod = 'index';
        }

        $tag = app(Loader::class)->load($name, [
            'parser'     => null,
            'params'     => $this->params,
            'content'    => '',
            'context'    => $this->context,
            'tag'        => $name.':'.$originalMethod,
            'tag_method' => $originalMethod,
        ]);

        return $this->fetched = $tag->$method();
    }

    /**
     * Get the value as a string.
     *
     * @return string
     */
    public function __toString()
    {
        return (string) $this->fetch();
    }

    /**
     * Get the value as an array.
     *
     * @return Traversable
     */
    #[\ReturnTypeWillChange]
    public function getIterator()
    {
        $output = $this->fetch();

        return $output instanceof Traversable ? $output : new ArrayIterator($output);
    }

    /**
     * Allow calls to tag params.
     *
     * @param  string  $param
     * @param  array  $value
     * @return $this
     */
    public function param($param, $value = true)
    {
        $this->params[$param] = $value ?? true;

        return $this;
    }

    /**
     * Allow calls to multiple tag params.
     *
     * @param  array  $params
     * @return $this
     */
    public function params($params)
    {
        foreach ($params as $param => $value) {
            $this->param($param, $value);
        }

        return $this;
    }

    /**
     * Allow calls to tag params via dynamic method names.
     *
     * @param  string  $param  Param name
     * @param  array  $args  First arg will be param value
     * @return $this
     */
    public function __call($param, $args)
    {
        $this->param($param, $args[0] ?? true);

        return $this;
    }

    public function __get($key)
    {
        return $this->fetch()[$key];
    }

    /**
     * Determine if an item exists at an offset.
     *
     * @param  mixed  $key
     * @return bool
     */
    #[\ReturnTypeWillChange]
    public function offsetExists($key)
    {
        return isset($this->fetch()[$key]);
    }

    /**
     * Get an item at a given offset.
     *
     * @param  mixed  $key
     * @return mixed
     */
    #[\ReturnTypeWillChange]
    public function offsetGet($key)
    {
        return $this->fetch()[$key];
    }

    /**
     * Set the item at a given offset.
     *
     * @param  mixed  $key
     * @param  mixed  $value
     * @return void
     */
    #[\ReturnTypeWillChange]
    public function offsetSet($key, $value)
    {
        //
    }

    /**
     * Unset the item at a given offset.
     *
     * @param  string  $key
     * @return void
     */
    #[\ReturnTypeWillChange]
    public function offsetUnset($key)
    {
        unset($this->fetch()[$key]);
    }
}
