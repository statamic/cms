<?php

namespace Statamic\Tags;

use Statamic\Support\Str;
use Statamic\View\Antlers\Parser;

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
        $name = $this->name;

        if ($pos = strpos($name, ':')) {
            $originalMethod = substr($name, $pos + 1);
            $method = Str::camel($originalMethod);
            $name = substr($name, 0, $pos);
        } else {
            $method = $originalMethod = 'index';
        }

        $tag = app(Loader::class)->load($name, [
            'parser'    => app(Parser::class),
            'params'    => $this->params,
            'content'   => '',
            'context'   => $this->context,
            'tag'       => $name.':'.$originalMethod,
            'tag_method' => $originalMethod,
        ]);

        return $tag->$method();
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
     * @return \Traversable
     */
    public function getIterator()
    {
        return $this->fetch();
    }

    /**
     * Allow calls to tag params via method names.
     *
     * @param  string  $method  Param name
     * @param  array  $args  First arg will be param value
     * @return $this
     */
    public function __call($method, $args)
    {
        $this->params[$method] = $args[0] ?? true;

        return $this;
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
