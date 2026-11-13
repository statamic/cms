<?php

namespace Statamic\Stache\Cache;

use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use ReflectionClass;
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
        if (! method_exists($item, '__sleep')) {
            return null;
        }

        // Also resolves anything that serializing would, like a slug that's still a closure.
        $keep = $item->__sleep();

        $properties = (fn () => get_object_vars($this))->call($item);

        $encoded = [];

        foreach ($keep as $property) {
            [$encodable, $value] = self::encodeValue($properties[$property] ?? null);

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

    private static function encodeValue($value): array
    {
        if ($value instanceof CarbonInterface) {
            return [true, ['__carbon' => [get_class($value), $value->format('Y-m-d H:i:s.u'), $value->getTimezone()->getName()]]];
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

        if (isset($value['__carbon'])) {
            [$class, $time, $timezone] = $value['__carbon'];

            return $class::createFromFormat('Y-m-d H:i:s.u', $time, $timezone);
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
            && (array_key_exists('__carbon', $value) || array_key_exists('__collection', $value));
    }
}
