<?php

namespace Statamic\Http\Resources;

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
        TermResource::class,
        UserResource::class,
    ];

    /**
     * Map resource implementations.
     *
     * @param array $resources
     */
    public static function map($resources)
    {
        collect($resources)
            ->filter(function ($class, $bindable) {
                return in_array($bindable, static::STATAMIC_RESOURCES);
            })
            ->each(function ($class) {
                if (! is_subclass_of($class, JsonResource::class)) {
                    throw new JsonResourceException("[{$class}] must be a subclass of " . JsonResource::class);
                }
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
}
