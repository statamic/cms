<?php

namespace Statamic\Extend;

use Statamic\Tags\Tags;
use Statamic\View\Antlers\Language\Analyzers\NodeTypeAnalyzer;

trait RegistersItself
{
    public static function register()
    {
        $key = self::class;
        $extensions = app('statamic.extensions');

        $extensions[$key] = with($extensions[$key] ?? collect(), function ($bindings) {
            $bindings[static::handle()] = static::class;

            if (method_exists(static::class, 'aliases')) {
                foreach (static::aliases() as $alias) {
                    $bindings[$alias] = static::class;
                }
            }

            return $bindings;
        });

        self::updateRegisteredEnvironmentDetails();
    }

    private static function updateRegisteredEnvironmentDetails(): void
    {
        // This static property will be set when
        // ViewServiceProvider is registered.
        if (NodeTypeAnalyzer::$environmentDetails == null) {
            return;
        }

        if (self::class != Tags::class || ! app()->has('statamic.tags')) {
            return;
        }

        // The static $environmentDetails references a singleton.
        // We will keep the registered tag names updated here.
        NodeTypeAnalyzer::$environmentDetails->setTagNames(app('statamic.tags')->keys()->all());
    }
}
