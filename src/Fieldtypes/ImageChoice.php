<?php

namespace Statamic\Fieldtypes;

use Faker\Generator;
use Statamic\Facades\Asset;
use Statamic\Facades\AssetContainer;
use Statamic\Fields\Fieldtype;
use Statamic\Fields\SampleValue;
use Statamic\Support\Arr;
use Statamic\Support\Str;

class ImageChoice extends Fieldtype
{
    protected $selectable = false;

    public function preload(): array
    {
        return [
            'options' => $this->normalizedOptions(),
        ];
    }

    public function preProcess($data)
    {
        return Arr::wrap($data);
    }

    public function process($data)
    {
        $data = Arr::wrap($data);

        return $this->config('multiple') ? $data : ($data[0] ?? null);
    }

    public function extraRenderableFieldData(): array
    {
        return [
            'options' => $this->normalizedOptions(),
        ];
    }

    private function normalizedOptions(): array
    {
        return collect($this->config('options'))
            ->filter(fn ($option) => is_array($option))
            ->reject(fn (array $option): bool => $option['hidden'] ?? false)
            ->values()
            ->map(function (array $option, int $index): ?array {
                $key = $option['key'] ?? null;

                if (! $key) {
                    return null;
                }

                return [
                    'key' => $key,
                    'label' => $option['label'] ?? $key,
                    'image' => $this->resolveImageUrl($option['image'] ?? null),
                    'letter' => chr(65 + $index),
                ];
            })
            ->filter()
            ->values()
            ->all();
    }

    private function resolveImageUrl(mixed $image): ?string
    {
        if (blank($image)) {
            return null;
        }

        if (is_array($image)) {
            $image = Arr::first($image);
        }

        if (! is_string($image)) {
            return null;
        }

        if (filter_var($image, FILTER_VALIDATE_URL) || str_starts_with($image, '/')) {
            return $image;
        }

        if (Str::contains($image, '::')) {
            return Asset::find($image)?->url();
        }

        return AssetContainer::all()
            ->map(fn ($container) => $container->asset($image))
            ->filter()
            ->first()
            ?->url();
    }

    public function previewValue(): mixed
    {
        $keys = SampleValue::optionKeys($this->field());

        return $this->field()->get('multiple')
            ? array_slice($keys, 0, 1)
            : ($keys[0] ?? null);
    }

    public function fakeValue(Generator $faker): mixed
    {
        if ($this->field()->get('multiple')) {
            return $this->randomOptionKeys($faker);
        }

        $keys = SampleValue::optionKeys($this->field());

        return $keys === [] ? null : $faker->randomElement($keys);
    }

    private function randomOptionKeys(Generator $faker): array
    {
        $keys = SampleValue::optionKeys($this->field());

        if ($keys === []) {
            return [];
        }

        return $faker->randomElements($keys, $faker->numberBetween(1, min(2, count($keys))));
    }
}
