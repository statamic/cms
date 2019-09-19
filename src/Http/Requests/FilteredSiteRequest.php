<?php

namespace Statamic\Http\Requests;

use Statamic\Facades\Site;

class FilteredSiteRequest extends FilteredRequest
{
    protected function prepareForValidation()
    {
        parent::prepareForValidation();

        if (! $this->filters->has('site') && Site::hasMultiple()) {
            $this->filters['site'] = ['value' => Site::selected()->handle()];
        }
    }
}
