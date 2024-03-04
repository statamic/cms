<?php

namespace Statamic\Stache\Repositories;

use Statamic\Contracts\Globals\GlobalVariablesRepository as RepositoryContract;
use Statamic\Contracts\Globals\Variables;
use Statamic\Globals\VariablesCollection;
use Statamic\Stache\Stache;
use Statamic\Support\Str;

class GlobalVariablesRepository implements RepositoryContract
{
    protected $stache;
    protected $store;

    public function __construct(Stache $stache)
    {
        $this->stache = $stache;
        $this->store = $stache->store('global-variables');
    }

    public function all(): VariablesCollection
    {
        $keys = $this->store->paths()->keys();

        return VariablesCollection::make($this->store->getItems($keys));
    }

    public function find($id): ?Variables
    {
        return $this->store->getItem($id);
    }

    public function whereSet($handle): VariablesCollection
    {
        return $this
            ->all()
            ->filter(fn ($variable) => Str::before($variable->id(), '::') == $handle)
            ->values();
    }

    public function save($variable)
    {
        $this->store->save($variable);
    }

    public function delete($variable)
    {
        $this->store->delete($variable);
    }

    public static function bindings(): array
    {
        return [
            Variables::class => \Statamic\Globals\Variables::class,
        ];
    }
}
