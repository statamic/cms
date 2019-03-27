<?php

namespace Statamic\Http\Requests;

use Statamic\API\Site;

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
