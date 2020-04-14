<?php

namespace Statamic\Extend;

use Facades\GuzzleHttp\Client;
use Statamic\Facades\Addon as AddonAPI;
use Illuminate\Support\Facades\Cache;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Pagination\LengthAwarePaginator;

class Marketplace
{
    /**
     * @var string
     */
    const API_PREFIX = 'api/v1/marketplace';

    /**
     * @var int
     */
    const CACHE_FOR_MINUTES = 60;

    /**
     * @var string
     */
    protected $domain = 'https://statamic.com';

    /**
     * @var bool
     */
    protected $verifySsl = true;

    /**
     * @var bool
     */
    protected $addLocalData = true;

    /**
     * @var string
     */
    protected $filter;

    /**
     * @var string
     */
    protected $searchQuery;

    /**
     * @var array
     */
    protected $payload;

    /**
     * Instantiate marketplace API wrapper.
     */
    public function __construct()
    {
        if ($domain = env('STATAMIC_DOMAIN')) {
            $this->domain = $domain;
            $this->verifySsl = false;
        }
    }

    /**
     * Query and cache payload from statamic.com marketplace API, then add local data to payload.
     *
     * @return $this
     */
    public function query()
    {
        if ($this->payload) {
            return $this;
        }

        $this->payload = Cache::rememberWithExpiration('marketplace-addons', function () {
            try {
                return [static::CACHE_FOR_MINUTES => $this->apiRequest('addons')];
            } catch (RequestException $exception) {
                return [5 => ['data' => []]];
            }
        });

        if ($this->addLocalData) {
            $this->addLocalDataToPayload();
            $this->addLocalDevelopmentAddonsToPayload();
        }

        return $this;
    }

    /**
     * Query without local data.
     *
     * @return $this
     */
    public function withoutLocalData()
    {
        $this->addLocalData = false;

        return $this;
    }

    /**
     * Set filter.
     *
     * @param mixed $filter
     * @return $this
     */
    public function filter($filter)
    {
        $this->filter = $filter;

        return $this;
    }

    /**
     * Set search query.
     *
     * @param mixed $searchQuery
     * @return $this
     */
    public function search($searchQuery)
    {
        $this->searchQuery = $searchQuery;

        return $this;
    }

    /**
     * Get addons.
     *
     * @return mixed
     */
    public function get()
    {
        $this->query();

        if ($this->filter) {
            $this->filterPayload();
        }

        if ($this->searchQuery) {
            $this->searchPayload();
        }

        return $this->payload;
    }

    /**
     * Show addon.
     *
     * @param string $addon
     * @return mixed
     */
    public function show($addon)
    {
        return Cache::rememberWithExpiration("marketplace-addons/{$addon}", function () use ($addon) {
            try {
                return [static::CACHE_FOR_MINUTES => $this->apiRequest("addons/{$addon}")];
            } catch (RequestException $exception) {
                return [5 => null];
            }
        });
    }

    /**
     * Get paginated addons.
     *
     * @param int $perPage
     * @return mixed
     */
    public function paginate($perPage)
    {
        $data = collect($this->get()['data']);

        $currentPage = request()->input('page', 1);
        $items = $data->forPage($currentPage, $perPage)->values();
        $total = $data->count();
        $options = ['path' => collect(explode('?', request()->getUri()))->first()];

        $paginator = new LengthAwarePaginator($items, $total, $perPage, $currentPage, $options);

        return JsonResource::collection($paginator)->additional($this->installedMeta());
    }

    /**
     * Find addon by package name (ie. 'vendor/package').
     *
     * @param string $package
     * @return mixed
     */
    public function findByPackageName($package)
    {
        return collect($this->get()['data'])->first(function ($addon) use ($package) {
            return strtolower(data_get($addon, 'variants.0.package')) === strtolower($package);
        });
    }

    /**
     * Send API request.
     *
     * @param string $endpoint
     * @param string $method
     * @return mixed
     */
    protected function apiRequest($endpoint, $method = 'GET')
    {
        $response = Client::request($method, $this->buildEndpoint($endpoint), [
            'verify' => $this->verifySsl,
            'query' => [
                'statamicVersion' => 3,
            ],
        ]);

        return json_decode($response->getBody(), true);
    }

    /**
     * Build api endpoint.
     *
     * @param string $uri
     * @return string
     */
    protected function buildEndpoint($endpoint)
    {
        return collect([$this->domain, self::API_PREFIX, $endpoint])->implode('/');
    }

    /**
     * Add local data to whole payload.
     */
    protected function addLocalDataToPayload()
    {
        $this->payload['data'] = collect($this->payload['data'])->map(function ($addon) {
            return $this->addLocalDataToAddon($addon);
        });
    }

    /**
     * Add local data to addon paylod.
     *
     * @param array $addon
     * @return array
     */
    protected function addLocalDataToAddon($addon)
    {
        return array_merge($addon, [
            'installed' => AddonAPI::all()->keys()->contains($addon['variants'][0]['package']),
        ]);
    }

    /**
     * Add local development addons to payload.
     */
    protected function addLocalDevelopmentAddonsToPayload()
    {
        AddonAPI::all()->reject->marketplaceProductId()->each(function ($addon) {
            $this->payload['data'][] = $this->buildAddonPayloadFromLocalData($addon);
        });
    }

    /**
     * Build addon payload from local data.
     *
     * @param Addon $addon
     * @return array
     */
    protected function buildAddonPayloadFromLocalData(Addon $addon)
    {
        return [
            'id' => $addon->id(),
            'name' => $addon->name(),
            'variants' => [
                [
                    'id' => $addon->id() . '-variant',
                    'number' => 1,
                    'description' => 'N/A',
                    'assets' => [],
                    'package' => $addon->package(),
                ]
            ],
            'seller' => [
                'id' => $addon->id() . '-seller',
                'name' => 'NA',
                'website' => null,
                'avatar' => null,
            ],
            'installed' => true,
        ];
    }

    /**
     * Filter payload.
     */
    protected function filterPayload()
    {
        if ($this->filter === 'installable') {
            $this->payload['data'] = collect($this->payload['data'])->reject->installed->values()->all();
        } elseif ($this->filter === 'installed') {
            $this->payload['data'] = collect($this->payload['data'])->filter->installed->values()->all();
        }
    }

    /**
     * Search payload.
     */
    protected function searchPayload()
    {
        $this->payload['data'] = collect($this->payload['data'])
             ->filter(function ($addon) {
                 return $this->searchProperty($addon['name'])
                     || $this->searchProperty($addon['seller']['name'] ?? true);
             })
             ->values()
             ->all();
    }

    /**
     * Search property.
     *
     * @param mixed $property
     * @return bool
     */
    protected function searchProperty($property)
    {
        if ($property === true) {
            return true;
        }

        return collect(explode(' ', $this->searchQuery))
            ->filter()
            ->map(function ($term) use ($property) {
                return str_contains(strtolower($property), $term);
            })
            ->filter()
            ->isNotEmpty();
    }

    /**
     * Get installed addon meta.
     *
     * @return array
     */
    protected function installedMeta()
    {
        return [
            'meta' => [
                'installed' => AddonAPI::all()->keys()->all()
            ]
        ];
    }
}
