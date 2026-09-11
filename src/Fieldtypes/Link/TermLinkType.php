<?php

namespace Statamic\Fieldtypes\Link;

use Statamic\Contracts\Data\Localization;
use Statamic\Facades;
use Statamic\Facades\Blink;
use Statamic\Facades\Site;
use Statamic\Fields\Field;
use Statamic\Support\Arr;

use function Statamic\trans as __;

class TermLinkType extends LinkType
{
    protected ?string $icon = 'taxonomies';

    public function title(): string
    {
        return __('Term');
    }

    public function configFieldItems(): array
    {
        return [
            'taxonomies' => [
                'display' => __('Taxonomies'),
                'instructions' => __('statamic::fieldtypes.link.config.taxonomies'),
                'type' => 'taxonomies',
                'mode' => 'select',
                'width' => '50',
            ],
        ];
    }

    public function resolve(string $id, $parent = null, bool $localize = false): mixed
    {
        if (! $term = Facades\Term::find($id)) {
            return null;
        }

        if (! $localize) {
            return $term;
        }

        $site = $parent instanceof Localization
            ? $parent->locale()
            : Site::current()->handle();

        return $term->in($site) ?? $term;
    }

    public function fieldtype(Field $field): array
    {
        return [
            'type' => 'terms',
            'max_items' => 1,
            'create' => false,
            'taxonomies' => $this->taxonomies($field),
        ];
    }

    private function taxonomies(Field $field): array
    {
        $taxonomies = $field->get('taxonomies');

        if (empty($taxonomies)) {
            $site = Site::current()->handle();

            $taxonomies = Blink::once('routable-taxonomy-handles-'.$site, function () use ($site) {
                return Facades\Taxonomy::all()->reject(function ($taxonomy) use ($site) {
                    return is_null($taxonomy->termRoute($site));
                })->map->handle()->values()->all();
            });
        }

        return Arr::wrap($taxonomies);
    }
}
