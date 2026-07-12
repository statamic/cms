<?php

namespace Statamic\Http\Resources\API;

use Illuminate\Http\Resources\Json\JsonResource;
use Statamic\Exceptions\JsonResourceException;

class Resource
{
    /**
     * Mappable resources.
     *
     * @var array
     */
    const STATAMIC_RESOURCES = [
        AssetResource::class,
        EntryResource::class,
        FormResource::class,
        GlobalSetResource::class,
        TermResource::class,
        UserResource::class,
        TreeResource::class,
    ];

    /**
     * Map resource implementations.
     *
     * @param  array  $resources
     */
    public static function map($resources)
    {
        collect($resources)
            ->each(function ($class, $bindable) {
                static::validateBinding($bindable, $class);
            })
            ->each(function ($class, $bindable) {
                app()->bind($bindable, function () use ($class) {
                    return $class;
                });
            });
    }

    /**
     * Map default resource implementations.
     */
    public static function mapDefaults()
    {
        $resources = collect(static::STATAMIC_RESOURCES)
            ->reject(function ($resource) {
                return app()->has($resource);
            })
            ->keyBy(function ($resource) {
                return $resource;
            })
            ->all();

        static::map($resources);
    }

    /**
     * Validate binding.
     *
     * @param  string  $bindable
     * @param  string  $class
     *
     * @throws JsonResourceException
     */
    protected static function validateBinding($bindable, $class)
    {
        if (! in_array($bindable, static::STATAMIC_RESOURCES)) {
            throw new JsonResourceException("[{$bindable}] is not a valid Statamic API resource");
        }

        if (! is_subclass_of($class, JsonResource::class)) {
            throw new JsonResourceException("[{$class}] must be a subclass of ".JsonResource::class);
        }
    }
}
