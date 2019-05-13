<?php

namespace Statamic\Stache\Repositories;

use Statamic\Stache\Stache;
use Statamic\Data\Globals\GlobalCollection;
use Statamic\Contracts\Data\Globals\GlobalSet;
use Illuminate\Support\Collection as IlluminateCollection;
use Statamic\Contracts\Data\Repositories\GlobalRepository as RepositoryContract;

class GlobalRepository implements RepositoryContract
{
    protected $stache;
    protected $store;

    public function __construct(Stache $stache)
    {
        $this->stache = $stache;
        $this->store = $stache->store('globals');
    }

    public function make()
    {
        return new \Statamic\Data\Globals\GlobalSet;
    }

    public function all(): GlobalCollection
    {
        return collect_globals($this->store->getItems());
    }

    public function find($id): ?GlobalSet
    {
        return $this->store->getItem($id);
    }

    public function findByHandle($handle): ?GlobalSet
    {
        return $this->find($this->store->getIdByHandle($handle));
    }

    public function save($global)
    {
        $localizable = $global->localizable();

        if (! $localizable->id()) {
            $localizable->id($this->stache->generateId());
        }

        // Clone the entry and all of its localizations so that any modifications to the
        // original objects aren't reflected in the cache until explicitly saved again.
        $localizable = clone $localizable;
        $localizable->localizations()->each(function ($localization) use ($localizable) {
            $localizable->addLocalization(clone $localization);
        });

        $this->store->insert($global);

        $this->store->save($global);
    }
}
