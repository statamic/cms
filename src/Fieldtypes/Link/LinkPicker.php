<?php

namespace Statamic\Fieldtypes\Link;

use Statamic\Exceptions\CollectionNotFoundException;
use Statamic\Facades\Blink;
use Statamic\Facades\Site;
use Statamic\Facades\User;
use Statamic\Fields\Field;
use Statamic\Fieldtypes\Assets\Assets as AssetsFieldtype;
use Statamic\Fieldtypes\Link;
use Statamic\Fieldtypes\Relationship;

class LinkPicker
{
    private Field $field;

    public function __construct(array $config)
    {
        $this->field = new Field('link', $config);
    }

    public function types(): array
    {
        // The cached payload contains site-dependent config (an entry link type falls back
        // to the current site's routable collections) and user-dependent meta (asset link
        // type permissions), so both need to be part of the key.
        $key = vsprintf('link-picker-types-%s-%s-%s', [
            Site::current()->handle(),
            User::current()?->id() ?? 'guest',
            md5(json_encode($this->field->config())),
        ]);

        return Blink::once($key, function () {
            return collect(Link::types())
                ->filter(fn (LinkType $type): bool => $type->visible($this->field))
                ->map(function (LinkType $type, string $handle): ?array {
                    if (! $config = $type->fieldtype($this->field)) {
                        return null;
                    }

                    $nestedField = new Field($handle, $config);
                    $nestedFieldtype = $nestedField->fieldtype();

                    try {
                        $meta = $nestedFieldtype->preload();
                    } catch (CollectionNotFoundException) {
                        $meta = [];
                    }

                    return [
                        'title' => $type->title(),
                        'component' => $nestedFieldtype->component(),
                        'config' => $nestedFieldtype->config(),
                        'meta' => $meta,
                    ];
                })
                ->filter()
                ->all();
        });
    }

    public function data(string $url): array
    {
        $ref = str($url)->after('statamic://')->before('?')->before('#')->toString();
        [$handle, $id] = explode('::', $ref, 2);

        return [$ref => $this->dataForType($handle, $id)];
    }

    private function dataForType(string $handle, string $id): ?array
    {
        if (! $linkType = Link::types()[$handle] ?? null) {
            return null;
        }

        if (! $config = $linkType->fieldtype($this->field)) {
            return null;
        }

        $nestedField = new Field($handle, $config);
        $nestedField->setValue([$id]);
        $fieldtype = $nestedField->fieldtype();

        // Both of these build their preload `data` from getItemData(), so we can get the
        // item without paying for the rest of the preload payload. Anything else may only
        // implement preload(), so it gets the original treatment.
        if ($fieldtype instanceof Relationship || $fieldtype instanceof AssetsFieldtype) {
            return $fieldtype->getItemData([$id])->first();
        }

        return $fieldtype->preload()['data'][0] ?? null;
    }
}
