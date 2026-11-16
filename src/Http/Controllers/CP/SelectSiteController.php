<?php

namespace Statamic\Http\Controllers\CP;

use Illuminate\Support\Uri;
use Statamic\Facades\Site;

use function Statamic\trans as __;

class SelectSiteController extends CpController
{
    public function select($handle)
    {
        if (! $site = Site::get($handle)) {
            return back()->withError('Invalid site.');
        }

        $this->authorize('view', $site);

        Site::setSelected($handle);

        return redirect($this->previousUrl($handle))->with('success', __('Site selected.'));
    }

    private function previousUrl(string $handle): string
    {
        $url = Uri::of(url()->previous());

        $filters = json_decode(base64_decode($url->query()->get('filters', '')), true);

        if (isset($filters['site'])) {
            $filters['site']['site'] = $handle;

            $url = $url->withQuery(['filters' => base64_encode(json_encode($filters))]);
        }

        return $url->value();
    }
}
