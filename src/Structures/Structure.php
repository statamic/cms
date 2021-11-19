<?php

namespace Statamic\Structures;

use Illuminate\Support\Traits\Tappable;
use Statamic\Contracts\Data\Augmentable;
use Statamic\Contracts\Structures\Structure as StructureContract;
use Statamic\Data\HasAugmentedData;
use Statamic\Facades;
use Statamic\Support\Str;
use Statamic\Support\Traits\FluentlyGetsAndSets;

abstract class Structure implements StructureContract, Augmentable
{
    use FluentlyGetsAndSets, Tappable;

    protected $title;
    protected $handle;
    protected $trees = [];
    protected $collection;
    protected $maxDepth;
    protected $expectsRoot = false;

    use HasAugmentedData;

    public function id()
    {
        return $this->handle();
    }

    public function handle($handle = null)
    {
        if (func_num_args() === 0) {
            return $this->handle;
        }

        $this->handle = $handle;

        return $this;
    }

    public function title($title = null)
    {
        return $this
            ->fluentlyGetOrSet('title')
            ->getter(function ($title) {
                return $title ?: Str::humanize($this->handle());
            })->args(func_get_args());
    }

    public function expectsRoot($expectsRoot = null)
    {
        return $this->fluentlyGetOrSet('expectsRoot')->args(func_get_args());
    }

    public function trees()
    {
        return collect($this->trees);
    }

    public function makeTree($site, $tree = [])
    {
        return $this->newTreeInstance()
            ->handle($this->handle())
            ->locale($site)
            ->tree($tree)
            ->syncOriginal();
    }

    abstract public function newTreeInstance();

    abstract public function existsIn($site);

    abstract public function in($site);

    abstract public function collections($collections = null);

    public function maxDepth($maxDepth = null)
    {
        return $this
            ->fluentlyGetOrSet('maxDepth')
            ->setter(function ($maxDepth) {
                return (int) $maxDepth ?: null;
            })->args(func_get_args());
    }

    public function validateTree(array $tree, string $locale): array
    {
        if (! $this->expectsRoot()) {
            return $tree;
        }

        throw_if(isset($tree[0]['children']), new \Exception('Root page cannot have children'));

        return $tree;
    }

    public function route(string $site): ?string
    {
        return null;
    }

    public function showUrl($params = [])
    {
        //
    }

    public function editUrl()
    {
        //
    }

    public function deleteUrl()
    {
        //
    }

    public static function __callStatic($method, $parameters)
    {
        return Facades\Structure::{$method}(...$parameters);
    }

    public function augmentedArrayData()
    {
        return [
            'title' => $this->title(),
            'handle' => $this->handle(),
        ];
    }
}
