<?php

namespace Statamic\Http\Resources;

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
