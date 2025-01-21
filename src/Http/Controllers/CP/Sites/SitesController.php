<?php

namespace Statamic\Http\Controllers\CP\Sites;

use Illuminate\Http\Request;
use Statamic\Facades\Site;
use Statamic\Http\Controllers\CP\CpController;

class SitesController extends CpController
{
    public function __construct()
    {
        $this->middleware(\Illuminate\Auth\Middleware\Authorize::class.':configure sites');
    }

    public function edit()
    {
        $blueprint = Site::blueprint();

        $fields = $blueprint
            ->fields()
            ->addValues($this->values())
            ->preProcess();

        return view('statamic::sites.configure', [
            'blueprint' => $blueprint->toPublishArray(),
            'values' => $fields->values(),
            'meta' => $fields->meta(),
        ]);
    }

    private function values(): array
    {
        $sites = collect(Site::config())
            ->map(fn ($site, $handle) => array_merge(['handle' => $handle], $site))
            ->values()
            ->all();

        if (! Site::multiEnabled()) {
            return $sites[0];
        }

        return ['sites' => $sites];
    }

    public function update(Request $request)
    {
        $blueprint = Site::blueprint();

        $fields = $blueprint
            ->fields()
            ->addValues($request->all());

        $fields->validate();

        $values = $fields
            ->process()
            ->values()
            ->all();

        // Normalize form values to sites config, since we always want array of sites keyed by handle, etc.
        $sites = collect(Site::multiEnabled() ? $values['sites'] : [$values])
            ->keyBy('handle')
            ->transform(function ($site) {
                return collect($site)
                    ->except(['id', 'handle'])
                    ->filter()
                    ->all();
            })
            ->all();

        Site::setSites($sites)->save();

        return response('', 204);
    }
}
