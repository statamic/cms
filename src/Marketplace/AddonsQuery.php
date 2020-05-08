<?php

namespace Statamic\Marketplace;

use Facades\Statamic\Marketplace\Client;
use Illuminate\Pagination\Paginator;
use Statamic\Extensions\Pagination\LengthAwarePaginator;
use Statamic\Facades\Addon;

class AddonsQuery
{
    protected $search;
    protected $page = 1;

    public function search($search)
    {
        $this->search = $search;

        return $this;
    }

    public function page($page)
    {
        $this->page = $page;

        return $this;
    }

    public function installed(bool $installed)
    {
        $this->installed = $installed;

        return $this;
    }

    public function get()
    {
        $installed = $this->installedVariants();

        $params = [
            'statamicVersion' => 3,
            'page' => $this->page,
            'search' => $this->search,
        ];

        if ($this->installed) {
            $params['filter'] = ['variants' => $installed->join(',')];
        }

        $addons = Client::get('addons', $params)['data'];

        return collect($addons)->map(function ($addon) use ($installed) {
            return $addon + ['installed' => $installed->contains($addon['variant_id'])];
        });
    }

    public function paginate()
    {
        return new LengthAwarePaginator($items = $this->get(), $items->count(), 15, $this->page, [
            'path' => Paginator::resolveCurrentPath(),
        ]);
    }

    private function installedVariants()
    {
        return Addon::all()->map->marketplaceVariantId()->filter();
    }
}
