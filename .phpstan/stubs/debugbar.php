<?php

/**
 * Minimal symbol stubs for php-debugbar/php-debugbar, an optional runtime
 * integration (via barryvdh/laravel-debugbar) that this package never
 * requires as a real dependency. Statamic\View\Debugbar\* classes are only
 * instantiated behind class_exists()/isEnabled() guards.
 *
 * These declarations exist solely so PHPStan can resolve the
 * extends/implements relationships in that code (see scanFiles in
 * phpstan.dist.neon) — they are never autoloaded or executed.
 *
 * Signatures mirror php-debugbar/php-debugbar ^3.8 (src/DataCollector/*).
 */

namespace DebugBar\DataCollector;

interface DataCollectorInterface
{
    public function collect(): array;

    public function getName(): string;
}

interface AssetProvider
{
    public function getAssets(): array;
}

interface Renderable extends DataCollectorInterface
{
    public function getWidgets(): array;
}

interface Resettable
{
    public function reset(): void;
}

abstract class DataCollector implements DataCollectorInterface
{
    //
}

class ConfigCollector extends DataCollector implements Renderable, Resettable
{
    protected string $name;

    protected array $data;

    public function __construct(array $data = [], string $name = 'config')
    {
        $this->name = $name;
    }

    public function reset(): void
    {
        //
    }

    public function setData(array $data): void
    {
        //
    }

    public function collect(): array
    {
        return [];
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getWidgets(): array
    {
        return [];
    }
}
