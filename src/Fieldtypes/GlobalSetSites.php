<?php

namespace Statamic\Fieldtypes;

use Illuminate\Contracts\Validation\Rule as ValidationRule;
use Statamic\Fields\Fieldtype;

use function Statamic\trans as __;

class GlobalSetSites extends Fieldtype
{
    protected $selectable = false;

    protected function configFieldItems(): array
    {
        return [
            'origins' => [
                'display' => __('Origins'),
                'type' => 'toggle',
                'default' => true,
            ],
        ];
    }

    public function process($data)
    {
        if ($this->config('origins', true)) {
            return $data;
        }

        return collect($data ?? [])
            ->filter(fn ($site) => is_string($site) ? filled($site) : ($site['enabled'] ?? false))
            ->map(fn ($site) => is_string($site) ? $site : $site['handle'])
            ->filter()
            ->values()
            ->all();
    }

    public function rules(): array
    {
        $rules = [
            $this->atLeastOneSiteEnabledRule(),
        ];

        if ($this->config('origins', true)) {
            $rules[] = $this->cannotAllHaveOriginsRule();
            $rules[] = $this->originsMustBeEnabledRule();
        }

        return $rules;
    }

    private function atLeastOneSiteEnabledRule()
    {
        return new class implements ValidationRule
        {
            public function passes($attribute, $value)
            {
                return collect($value)->contains(function ($site) {
                    return is_string($site) ? filled($site) : ($site['enabled'] ?? false);
                });
            }

            public function message()
            {
                return __('statamic::validation.at_least_one_site_enabled');
            }
        };
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
