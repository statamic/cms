<?php

namespace Statamic\Forms\Fieldtypes;

use Facades\Statamic\Fields\FieldRepository;
use Illuminate\Support\Facades\Log;
use Statamic\Facades\Blink;
use Statamic\Forms\Fields\FormField;
use Statamic\Forms\Fields\FormFieldtype;
use Statamic\Support\Arr;

use function Statamic\trans as __;

class Group extends FormFieldtype
{
    protected static $fieldtype = 'group';
    protected $icon = 'fieldtype-group';
    protected $categories = ['structure'];

    private const UNSUPPORTED_CHILD_TYPES = ['assets', 'files', 'upload', 'form_upload'];

    protected function configFieldItems(): array
    {
        return [
            'fields' => [
                'display' => __('Fields'),
                'instructions' => __('statamic::fieldtypes.group.config.fields'),
                'type' => 'form_group_fields',
                'full_width_setting' => true,
            ],
        ];
    }

    public function toFieldArray(): array
    {
        return [
            'type' => 'group',
            'fields' => $this->children(),
            ...Arr::except($this->config(), ['type', 'fields']),
        ];
    }

    private function children(): array
    {
        return collect($this->config('fields', []))
            ->reject(fn ($child) => $this->isUnsupportedChild($child))
            ->map(function ($child) {
                if (! $this->isInlineChild($child)) {
                    return $child;
                }

                return [
                    ...$child,
                    'field' => Arr::removeNullValues((new FormField($child['handle'], $child['field']))->toFieldArray()),
                ];
            })
            ->values()
            ->all();
    }

    private function childType($child): ?string
    {
        if ($this->isInlineChild($child)) {
            return $child['field']['type'] ?? null;
        }

        if (is_array($child) && isset($child['handle']) && is_string($child['field'] ?? null)) {
            return FieldRepository::find($child['field'])?->type();
        }

        return null;
    }

    private function isInlineChild($child): bool
    {
        return is_array($child) && isset($child['handle']) && is_array($child['field'] ?? null);
    }

    private function isUnsupportedChild($child): bool
    {
        if (! in_array($this->childType($child), self::UNSUPPORTED_CHILD_TYPES)) {
            return false;
        }

        $group = $this->field()?->handle();

        Blink::once("form-group-unsupported-child-{$group}.{$child['handle']}", function () use ($group, $child) {
            Log::warning("Form group field [{$group}] contains an upload field [{$child['handle']}]. Uploads aren't supported inside groups, so it won't be rendered or submitted.");

            return true;
        });

        return true;
    }
}
