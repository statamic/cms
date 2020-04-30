<?php

namespace Statamic;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Log;
use Statamic\Facades\Addon;
use Statamic\Facades\Config;

class Outpost
{
    /**
     * The URL of the Outpost.
     */
    const ENDPOINT = 'https://outpost.statamic.com/v2/query';

    /**
     * Where the cached response will be stored.
     */
    const RESPONSE_CACHE_KEY = 'outpost_response';

    /**
     * @var Illuminate\Http\Request
     */
    private $request;

    /**
     * @var array
     */
    private $response;

    /**
     * Create a new Outpost instance.
     *
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Radio into the Outpost.
     *
     * Tampering with outgoing API call will cause Statamic to consider your license invalid.
     * We’ll also send our flying police monkeys to your office to throw poop at you. Maybe.
     *
     * @return array
     * @throws PoopException maybe.
     */
    public function radio()
    {
        if ($this->hasCachedResponse()) {
            return $this->response = $this->getCachedResponse();
        }

        $this->performRequest();

        $this->cacheResponse();

        return $this->response;
    }

    public function hasSuccessfulResponse()
    {
        return ! array_get($this->response, 'default_response');
    }

    public function getLicenseKey()
    {
        return Config::getLicenseKey();
    }

    public function hasLicenseKey()
    {
        return $this->getLicenseKey() != null;
    }

    /**
     * Is the site's license key valid?
     *
     * @return bool
     */
    public function isLicenseValid()
    {
        return array_get($this->response, 'license_valid');
    }

    public function areAddonLicensesValid()
    {
        foreach (array_get($this->response, 'addons', []) as $addon) {
            if (! $addon['licensed']) {
                return false;
            }
        }

        return true;
    }

    public function isAddonLicenseValid(\Statamic\Extend\Addon $addon)
    {
        $addons = collect(array_get($this->response, 'addons', []));

        $match = $addons->where('addon', $addon->id())->first();

        return $match['licensed'];
    }

    /**
     * Is the site in trial mode?
     *
     * @return bool
     */
    public function isTrialMode()
    {
        if ($this->isLicenseValid()) {
            return false;
        }

        return ! $this->isOnPublicDomain();
    }

    /**
     * Is the site on a publicly accessible domain?
     *
     * @return bool
     */
    public function isOnPublicDomain()
    {
        return array_get($this->response, 'public_domain');
    }

    /**
     * Is the site on their designated licensed domain?
     *
     * @return bool
     */
    public function isOnCorrectDomain()
    {
        if (! $this->isOnPublicDomain()) {
            return true;
        }

        return array_get($this->response, 'correct_domain');
    }

    public function getLicenseDomain()
    {
        return array_get($this->response, 'domain');
    }

    /**
     * Is there an update available?
     *
     * @return bool
     */
    public function isUpdateAvailable()
    {
        return version_compare(Statamic::version(), $this->getLatestVersion(), '<');
    }

    /**
     * How many updates are between the installed version and the latest.
     *
     * @return int
     */
    public function getUpdateCount()
    {
        return $this->isUpdateAvailable() ? array_get($this->response, 'update_count') : 0;
    }

    /**
     * What's the latest version?
     *
     * @return string
     */
    public function getLatestVersion()
    {
        return array_get($this->response, 'latest_version');
    }

    /**
     * Perform the request to the Outpost.
     *
     * @return void
     */
    private function performRequest()
    {
        // Set up a default response in case of a failed communication with the Outpost.
        $response = $this->getDefaultResponse();

        try {
            $client = new Client;
            $response = $client->request('POST', self::ENDPOINT, ['json' => $this->getPayload(), 'timeout' => 5]);
            $response = json_decode($response->getBody()->getContents(), true);
        } catch (RequestException $e) {
            Log::notice("Couldn't reach the Statamic Outpost.");
        } catch (Exception $e) {
            Log::error('Ran into an issue when contacting the Statamic Outpost.');
        }

        $this->response = $response;
    }

    /**
     * Cache the response. An hour feels about right.
     *
     * @return void
     */
    private function cacheResponse()
    {
        Cache::put(self::RESPONSE_CACHE_KEY, $this->response, now()->addMinutes(60));
    }

    /**
     * Check if a response has been cached, and whether it should be used.
     *
     * @return bool
     */
    private function hasCachedResponse()
    {
        // No cache? That was simple.
        if (! Cache::has(self::RESPONSE_CACHE_KEY)) {
            return false;
        }

        // Changing the license key essentially invalidates the cache
        if ($this->getLicenseKey() !== array_get($this->getCachedResponse(), 'license_key')) {
            return false;
        }

        return true;
    }

    /**
     * Get the cached response.
     *
     * @return array
     */
    private function getCachedResponse()
    {
        return Cache::get(self::RESPONSE_CACHE_KEY);
    }

    public function clearCachedResponse()
    {
        Cache::forget(self::RESPONSE_CACHE_KEY);
    }

    /**
     * Get a default response to use if the request can't be made.
     *
     * @return array
     */
    private function getDefaultResponse()
    {
        return [
            'default_response' => true,
            'license_key'      => $this->getLicenseKey(),
            'latest_version'   => Statamic::version(),
            'update_available' => false,
            'update_count'     => 0,
            'license_valid'    => false,
        ];
    }

    /**
     * Get the payload to be sent to the Outpost.
     *
     * @return array
     */
    private function getPayload()
    {
        return [
            'license_key' => $this->getLicenseKey(),
            'version'     => Statamic::version(),
            'php_version' => PHP_VERSION,
            'request'     => [
                'domain'  => request()->server('HTTP_HOST'),
                'ip'      => request()->ip(),
                'port'    => request()->getPort(),
            ],
            'addons' => $this->getAddonsPayload(),
        ];
    }

    private function getAddonsPayload()
    {
        return Addon::all()->map(function ($addon) {
            return [
                'addon' => $addon->id(),
                'version' => $addon->version(),
                'license_key' => $addon->licenseKey(),
            ];
        })->all();
    }
}
