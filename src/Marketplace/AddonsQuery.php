<?php

namespace Statamic\Marketplace;

use Facades\Statamic\Marketplace\Client;
use Illuminate\Pagination\Paginator;
use Statamic\Extensions\Pagination\LengthAwarePaginator;
use Statamic\Facades\Addon;

class AddonsQuery
{
    protected $search;
    protected $installed = false;
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
        $installed = $this->installedProducts();

        $params = [
            'page' => $this->page,
            'search' => $this->search,
            'filter' => ['statamic' => 3],
        ];

        if ($this->installed) {
            if ($installed->isEmpty()) {
                return ['data' => [], 'meta' => ['total' => 0, 'per_page' => 15]];
            }

            $params['filter']['products'] = $installed->join(',');
        }

        $response = Client::get('addons', $params);

        $response['data'] = collect($response['data'])->map(function ($addon) use ($installed) {
            return $addon + [
                'installed' => $isInstalled = $installed->contains($addon['id']),
                'edition' => $isInstalled ? Addon::get($addon['package'])->edition() : null,
            ];
        })->all();

        return $response;
    }

    public function paginate()
    {
        $response = $this->get();

        return new LengthAwarePaginator(
            $response['data'],
            $response['meta']['total'],
            $response['meta']['per_page'],
            $this->page,
            ['path' => Paginator::resolveCurrentPath()]
        );
    }

    private function installedProducts()
    {
        return Addon::all()->map->marketplaceId()->filter();
    }
}
