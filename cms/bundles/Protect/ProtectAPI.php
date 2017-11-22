<?php

namespace Statamic\Addons\Protect;

use Statamic\API\Arr;
use Statamic\API\URL;
use Statamic\API\Config;
use Statamic\Extend\API;

class ProtectAPI extends API
{
    /**
     * @param mixed $data
     */
    public function protect($data)
    {
        $url = URL::getCurrent();

        // Get the global protection configuration. This will be used for both site
        // wide protection, and merging into page specific protection schemas.
        $config = Config::get('system.protect', []);

        // If a type has been specified in the config, then it should be site-wide.
        if ($type = array_get($config, 'type')) {
            $scheme = array_get($config, $type, []);
            (new ProtectorManager($url, $scheme, true))->protect($type);
        }

        // Get the protection scheme from the page data, and call it a day if there isn't one.
        if (! $protect = array_get($data->toArray(), 'protect')) {
            return;
        }

        // A string may be specified to simplify yaml. eg. `protect: logged_in`
        // Or, `true` may be specified to just flat-out block a page.
        if (is_string($protect) || $protect === true) {
            $protect = ['type' => $protect];
        }

        $type = $protect['type'];

        // The scheme defined on the page should be merged with the global config.
        // The page definition should take precedence.
        $scheme = array_merge(
            array_get($config, $type, []),
            Arr::except($protect, 'type')
        );

        (new ProtectorManager($url, $scheme, false))->protect($type);
    }
}
