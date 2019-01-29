<?php

namespace Statamic\Http\Requests;

use Statamic\API\Site;

class FilteredSiteRequest extends FilteredRequest
{
    protected function prepareForValidation()
    {
        parent::prepareForValidation();

        if (! $this->filters->has('site')) {
            $this->filters['site'] = Site::selected()->handle();
        }
    }
}
