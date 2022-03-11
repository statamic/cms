<?php

namespace Statamic\Modifiers;

use ArrayIterator;
use Exception;
use Statamic\Fields\Values;
use Statamic\Support\Arr;

class Modify implements \IteratorAggregate
{
    /**
     * @var mixed
     */
    protected $value;

    /**
     * @var array
     */
    protected $context = [];

    /**
     * @var Loader
     */
    private $loader;

    /**
     * Instantiate fluent modifier helper.
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
     * @param  mixed  $value
     * @return \Statamic\Modifiers\Modify
     */
    public function __invoke($value)
    {
        return static::value($value);
    }

    /**
     * Specify a value to start the modification chain.
     *
     * @param  mixed  $value
     * @return \Statamic\Modifiers\Modify
     */
    public static function value($value)
    {
        $instance = app(self::class);

        $instance->value = $value;

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
     * Get the raw value.
     *
     * @return mixed
     */
    public function fetch()
    {
        return $this->value;
    }

    /**
     * Get the value as a string.
     *
     * @return string
     *
     * @throws \Statamic\Modifiers\ModifierException
     */
    public function __toString()
    {
        if (! is_string($this->value) && ! method_exists($this->value, '__toString')) {
            throw new ModifierException(
                'Attempted to access modified value as a string, but encountered ['.get_class($this->value).']'
            );
        }

        return (string) $this->value;
    }

    /**
     * Get the value as an array.
     *
     * @return \Traversable
     *
     * @throws \Statamic\Modifiers\ModifierException
     */
    #[\ReturnTypeWillChange]
    public function getIterator()
    {
        if (! is_array($this->value)) {
            throw new ModifierException(sprintf(
                'Attempted to access modified value as an array, but encountered [%s]',
                is_string($this->value) ? 'string' : get_class($this->value)
            ));
        }

        return new ArrayIterator($this->fetch());
    }

    /**
     * Allow calls to modifiers via method names.
     *
     * @param  string  $method  Modifier name
     * @param  array  $args  Any parameters as arguments
     * @return $this
     */
    public function __call($method, $args)
    {
        $this->value = $this->modify($method, Arr::get($args, 0));

        return $this;
    }

    /**
     * Modify a value.
     *
     * @param  string  $modifier
     * @param  array  $params
     * @return mixed
     *
     * @throws ModifierException
     */
    public function modify($modifier, $params = [])
    {
        // Blade and/or raw PHP usage might pass a single parameter.
        // We should make sure it's always an array.
        $params = (array) $params;

        try {
            // Attempt to run the modifier. If it worked, awesome,
            // we'll have successfully returned a modified value.
            return $this->runModifier($modifier, $params);
        } catch (ModifierException $e) {
            // If this class explicitly raised an exception, it would've
            // been a ModifierException, so we'll just rethrow it since
            // we'll be catching it on the view side of things.
            $e->setModifier($modifier);
            throw $e;
        } catch (ModifierNotFoundException $e) {
            // Modifiers that don't exist shouldn't fail silently.
            // This exception will have a nice Ignition solution.
            throw $e;
        } catch (Exception $e) {
            // If a modifier's code raised an exception, we'll just
            // catch it here and rethrow it as a ModifierException.
            $e = new ModifierException($e->getMessage(), 0, $e);
            $e->setModifier($modifier);
            throw $e;
        }
    }

    /**
     * Run the modifier.
     *
     * We keep all the native bundled modifiers in one big juicy class
     * rather than a million separate files. First, we'll check there
     * then attempt to load a modifier in a regular addon location.
     *
     * @param  string  $modifier
     * @param  array  $params
     * @return mixed
     *
     * @throws \Exception
     */
    protected function runModifier($modifier, $params)
    {
        [$class, $method] = $this->loader->load($modifier);

        $value = $this->value;

        if ($value instanceof Values) {
            $value = $value->all();
        }

        return $class->$method($value, $params, $this->context);
    }
}
