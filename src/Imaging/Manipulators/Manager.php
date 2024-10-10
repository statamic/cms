<?php

namespace Statamic\Imaging\Manipulators;

use Closure;
use Illuminate\Container\Container;
use Statamic\Contracts\Imaging\Manipulator as Contract;

class Manager
{
    private array $customCreators = [];

    public function __construct(private Container $app)
    {
        //
    }

    public function manipulator(?string $name = null): Contract
    {
        $name = $name ?: $this->getDefaultManipulator();

        return $this->resolve($name);
    }

    private function resolve(string $name): Contract
    {
        if (! $config = $this->getConfig($name)) {
            throw new \InvalidArgumentException("Image manipulator [{$name}] is not defined.");
        }

        if (isset($this->customCreators[$config['driver']])) {
            return $this->customCreators[$config['driver']]($config);
        }

        return match ($config['driver']) {
            'glide' => new GlideManipulator($config),
            'imgix' => new ImgixManipulator($config),
            'cloudflare' => new CloudflareManipulator($config),
        };
    }

    private function getConfig(string $name): ?array
    {
        return $this->app['config']["statamic.image_manipulation.manipulators.{$name}"];
    }

    private function getDefaultManipulator(): string
    {
        return $this->app['config']['statamic.image_manipulation.default'];
    }

    public function extend(string $driver, Closure $callback): self
    {
        $this->customCreators[$driver] = $callback;

        return $this;
    }
}
