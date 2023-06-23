<?php

namespace Statamic\Stache\Repositories;

use Statamic\Contracts\Globals\GlobalVariableRepository as RepositoryContract;
use Statamic\Contracts\Globals\Variables;
use Statamic\Globals\VariableCollection;
use Statamic\Support\Str;
use Statamic\Stache\Stache;

class GlobalVariableRepository implements RepositoryContract
{
    protected $stache;
    protected $store;

    public function __construct(Stache $stache)
    {
        $this->stache = $stache;
        $this->store = $stache->store('global-variables');
    }

    public function make()
    {
        return app(Variables::class);
    }

    public function all(): VariableCollection
    {
        $keys = $this->store->paths()->keys();

        return VariableCollection::make($this->store->getItems($keys));
    }

    public function find($id): ?Variables
    {
        return $this->store->getItem($id);
    }

    public function findBySet($handle): ?VariableCollection
    {
        return $this->all()->filter(function ($variable) use ($handle) {
            return Str::before($variable->id(), '.') == $handle;
        });
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
