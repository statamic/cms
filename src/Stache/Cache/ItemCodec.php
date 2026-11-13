<?php

namespace Statamic\Stache\Cache;

use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use ReflectionClass;
use Serializable;
use Throwable;

/**
 * Turns an entry into a plain array and back, holding the same properties serializing it
 * would keep, so that it can be written as PHP code rather than as a serialized string.
 */
class ItemCodec
{
    /**
     * The item as an array of scalars and arrays, or null if it holds something that can't be one.
     */
    public static function encode(object $item): ?array
    {
        if (! self::serializesTheOrdinaryWay($item)) {
            return null;
        }

        // Also resolves anything that serializing would, like a slug that's still a closure.
        $keep = $item->__sleep();

        $properties = (fn () => get_object_vars($this))->call($item);

        $encoded = [];

        foreach ($keep as $property) {
            // Named but not there: a typed property nothing has been put in yet, which
            // serializing would leave that way.
            if (! array_key_exists($property, $properties)) {
                return null;
            }

            [$encodable, $value] = self::encodeValue($properties[$property]);

            if (! $encodable) {
                return null;
            }

            $encoded[$property] = $value;
        }

        return ['class' => get_class($item), 'properties' => $encoded];
    }

    public static function decode(array $row): object
    {
        // Made without its constructor and given its properties, as unserializing does.
        $item = (new ReflectionClass($row['class']))->newInstanceWithoutConstructor();

        $properties = array_map(self::decodeValue(...), $row['properties']);

        (function () use ($properties) {
            foreach ($properties as $property => $value) {
                $this->{$property} = $value;
            }
        })->call($item);

        return $item;
    }

    /**
     * Whether a value is made up of nothing but scalars, nulls, and arrays of them.
     *
     * A large array is serialized and looked through for an object, which is one pass in
     * PHP's own code where walking it here would be a function call for every value in it.
     * Something that only looks like one (a string that happens to hold the same characters)
     * is taken for one, which is the safe way to be wrong: it's kept as it would have been
     * by any other store. The serialized value is handed back for whoever needs it next.
     */
    public static function isPlain($value, ?string &$serialized = null): bool
    {
        if (! is_array($value)) {
            return $value === null || is_scalar($value);
        }

        try {
            $serialized = serialize($value);
        } catch (Throwable) {
            return false;
        }

        // An object, an enum, or a reference to another part of the value, where one can begin.
        return ! preg_match('/(?:^|[;{])(?:[OCE]:\d+:"|[Rr]:\d+;)/', $serialized);
    }

    /**
     * Whether an item is serialized by naming its properties and unserialized by having them
     * put back, which is what this class does in its place. One that takes a hand in either
     * (an entry class of an addon's, say) is left to be serialized.
     */
    private static function serializesTheOrdinaryWay(object $item): bool
    {
        static $classes = [];

        return $classes[get_class($item)] ??= method_exists($item, '__sleep')
            && ! $item instanceof Serializable
            && ! method_exists($item, '__serialize')
            && ! method_exists($item, '__unserialize')
            && ! method_exists($item, '__wakeup');
    }

    private static function encodeValue($value): array
    {
        // A date keeps more than its time (its timezone, its locale), so it's kept serialized.
        if ($value instanceof CarbonInterface) {
            return [true, ['__serialized' => serialize($value)]];
        }

        // Only a plain collection: a subclass would come back as something else.
        if ($value instanceof Collection && get_class($value) === Collection::class) {
            return self::isPlain($value->all()) ? [true, ['__collection' => $value->all()]] : [false, null];
        }

        if (self::isPlain($value) && ! self::looksEncoded($value)) {
            return [true, $value];
        }

        return [false, null];
    }

    private static function decodeValue($value)
    {
        if (! self::looksEncoded($value)) {
            return $value;
        }

        if (isset($value['__serialized'])) {
            return unserialize($value['__serialized']);
        }

        return new Collection($value['__collection']);
    }

    /**
     * An array that has the shape this class gives a date or a collection. One that's
     * really an item's own value isn't encoded, so it can't be mistaken for either.
     */
    private static function looksEncoded($value): bool
    {
        return is_array($value)
            && count($value) === 1
            && (array_key_exists('__serialized', $value) || array_key_exists('__collection', $value));
    }
}
