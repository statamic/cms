<?php

namespace Statamic\Fieldtypes;

use Facades\Statamic\Routing\ResolveRedirect;
use Statamic\Contracts\Entries\Collection;
use Statamic\Contracts\Entries\Entry;
use Statamic\Facades\GraphQL;
use Statamic\Fields\Field;
use Statamic\Fields\Fieldtype;
use Statamic\Fieldtypes\Link\ArrayableLink;
use Statamic\Fieldtypes\Link\LinkType;
use Statamic\GraphQL\Types\LinkValueType;
use Statamic\Support\Str;

use function Statamic\trans as __;

class Link extends Fieldtype
{
    use UpdatesReferences;
    protected $categories = ['relationship'];

    protected static array $types = [];

    public static function extend(string $handle, string $type): void
    {
        static::$types[$handle] = $type;

        if ($fields = static::resolveType($handle)->configFieldItems()) {
            static::appendConfigFields($fields);
        }
    }

    public static function resolveType(string $handle): LinkType
    {
        return app(static::$types[$handle])->setHandle($handle);
    }

    public static function types(): array
    {
        return collect(static::$types)
            ->keys()
            ->mapWithKeys(fn (string $handle): array => [$handle => static::resolveType($handle)])
            ->all();
    }

    protected function configFieldItems(): array
    {
        return [
            [
                'display' => __('Input Behavior'),
                'fields' => [
                    'default_option' => [
                        'display' => __('Default Option'),
                        'instructions' => __('statamic::fieldtypes.link.config.default_option'),
                        'type' => 'select',
                        'max_items' => 1,
                        'width' => '50',
                        'clearable' => true,
                        'placeholder' => __('Default'),
                        'options' => $this->defaultOptionOptions(),
                    ],
                ],
            ],
        ];
    }

    public function augment($value)
    {
        $localize = ! $this->canSelectAcrossSites();

        return new ArrayableLink(
            $value
                ? ResolveRedirect::item($value, $this->field->parent(), $localize)
                : null,
            ['select_across_sites' => $this->canSelectAcrossSites()]
        );
    }

    public function preProcessIndex($data)
    {
        if (! $data) {
            return null;
        }

        if ($data === '@child' && ! $this->field->parent() instanceof Entry) {
            return null;
        }

        if (! $item = ResolveRedirect::item($data, $this->field->parent())) {
            return null;
        }

        if (! ($url = is_object($item) ? $item->url() : $item)) {
            return null;
        }

        $linkType = $this->linkTypeFor($data);

        return [
            'type' => $data === '@child' ? 'child' : ($linkType ?? 'url'),
            'url' => $url,
            'icon' => match (true) {
                $data === '@child' => 'page',
                $linkType !== null => static::resolveType($linkType)->icon(),
                default => 'external-link',
            },
        ];
    }

    public function preload()
    {
        $value = $this->field->value();

        $linkType = $this->linkTypeFor($value);

        $url = ($value !== '@child' && ! $linkType) ? $value : null;

        $types = [];

        foreach (static::types() as $handle => $type) {
            if (! $type->visible($this->field)) {
                continue;
            }

            if (! $config = $type->fieldtype($this->field)) {
                continue;
            }

            $selected = $linkType === $handle ? Str::after($value, "{$handle}::") : null;

            $nestedField = new Field($handle, $config);
            $nestedField->setValue($selected);
            $nestedFieldtype = $nestedField->fieldtype();

            $types[$handle] = [
                'title' => $type->title(),
                'component' => $nestedFieldtype->component(),
                'config' => $nestedFieldtype->config(),
                'meta' => $nestedFieldtype->preload(),
                'selected' => $selected ? [$selected] : [],
            ];
        }

        return [
            'initialUrl' => $url,
            'initialOption' => $this->initialOption($value, $linkType),
            'showFirstChildOption' => $this->showFirstChildOption(),
            'types' => $types,
        ];
    }

    private function linkTypeFor($value): ?string
    {
        if (! $value) {
            return null;
        }

        foreach (static::types() as $type) {
            if (Str::startsWith($value, "{$type->handle()}::")) {
                return $type->handle();
            }
        }

        return null;
    }

    private function initialOption($value, ?string $matchedHandle)
    {
        if (! $value) {
            $fallback = $this->field->isRequired() && ! $this->field->hasSometimesRule() ? 'url' : null;
            $option = $this->field->get('default_option', $fallback);

            if ($option === 'first-child' && ! $this->showFirstChildOption()) {
                return $fallback;
            }

            if ($option && $option !== 'first-child' && $option !== 'url' && ! $this->isLinkTypeVisible($option)) {
                return $fallback;
            }

            return $option;
        }

        if ($value === '@child') {
            return 'first-child';
        }

        return $matchedHandle ?? 'url';
    }

    private function isLinkTypeVisible(string $handle): bool
    {
        $linkType = static::types()[$handle] ?? null;

        return $linkType?->visible($this->field) ?? false;
    }

    private function defaultOptionOptions(): array
    {
        $options = [];

        foreach (static::types() as $handle => $type) {
            $options[$handle] = $type->title();
        }

        $options['first-child'] = __('First Child');
        $options['url'] = __('URL');

        return $options;
    }

    private function showFirstChildOption()
    {
        $parent = $this->field()->parent();

        if ($parent instanceof Entry) {
            $collection = $parent->collection();
        } elseif ($parent instanceof Collection) {
            $collection = $parent;
        } else {
            return false;
        }

        return $collection->hasStructure() && $collection->structure()->maxDepth() !== 1;
    }

    public function toGqlType()
    {
        return GraphQL::type(LinkValueType::NAME);
    }

    private function canSelectAcrossSites(): bool
    {
        return $this->config('select_across_sites', false);
    }

    public function replaceAssetReferences($data, ?string $newValue, string $oldValue, string $container)
    {
        if ($this->config('container') !== $container) {
            return $data;
        }

        if ($data !== "asset::{$container}::{$oldValue}") {
            return $data;
        }

        return $newValue !== null ? "asset::{$container}::{$newValue}" : null;
    }
}
