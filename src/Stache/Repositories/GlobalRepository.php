<?php

namespace Statamic\Stache\Repositories;

use Statamic\Contracts\Globals\GlobalRepository as RepositoryContract;
use Statamic\Contracts\Globals\GlobalSet;
use Statamic\Facades\GlobalSetVariable;
use Statamic\Globals\GlobalCollection;
use Statamic\Stache\Stache;

class GlobalRepository implements RepositoryContract
{
    protected $stache;
    protected $store;

    public function __construct(Stache $stache)
    {
        $this->stache = $stache;
        $this->store = $stache->store('globals');
    }

    public function make($handle = null)
    {
        return app(GlobalSet::class)->handle($handle);
    }

    public function all(): GlobalCollection
    {
        $keys = $this->store->paths()->keys();

        return GlobalCollection::make($this->store->getItems($keys)->map(function ($set) {
            return $this->addLocalizations($set);
        }));
    }

    public function find($id): ?GlobalSet
    {
        return $this->addLocalizations($this->store->getItem($id));
    }

    public function findByHandle($handle): ?GlobalSet
    {
        $key = $this->store->index('handle')->items()->flip()->get($handle);

        return $this->find($key);
    }

    public function save($global)
    {
        $this->store->save($global);

        $global->localizations()->each->save();
    }

    public function delete($global)
    {
        $this->store->delete($global);

        $global->localizations()->each->delete();
    }

    public static function bindings(): array
    {
        return [
            GlobalSet::class => \Statamic\Globals\GlobalSet::class,
        ];
    }

    protected function addLocalizations($set)
    {
        if (! $set) {
            return $set;
        }

        GlobalSetVariable::findBySet($set->handle())
            ->each(function ($variable) use ($set) {
                $set->addLocalization($variable);
            });

        return $set;
    }
}
