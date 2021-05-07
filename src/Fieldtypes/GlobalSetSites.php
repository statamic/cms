<?php

namespace Statamic\Fieldtypes;

use Illuminate\Contracts\Validation\Rule as ValidationRule;
use Statamic\Fields\Fieldtype;

class GlobalSetSites extends Fieldtype
{
    protected $selectable = false;

    public function rules(): array
    {
        return [
            $this->cannotAllHaveOriginsRule(),
            $this->originsMustBeEnabledRule(),
        ];
    }

    private function cannotAllHaveOriginsRule()
    {
        return new class implements ValidationRule
        {
            public function passes($attribute, $value)
            {
                return collect($value)->map->origin->filter()->count() !== count($value);
            }

            public function message()
            {
                return __('statamic::validation.one_site_without_origin');
            }
        };
    }

    private function originsMustBeEnabledRule()
    {
        return new class implements ValidationRule
        {
            public function passes($attribute, $value)
            {
                $sites = collect($value)->keyBy->handle->filter->enabled;
                $origins = $sites->map->origin->filter();

                foreach ($origins as $origin) {
                    if (! $sites->has($origin)) {
                        return false;
                    }
                }

                return true;
            }

            public function message()
            {
                return __('statamic::validation.origin_cannot_be_disabled');
            }
        };
    }
}
