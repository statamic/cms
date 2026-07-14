<?php

namespace Statamic\Fieldtypes;

use Statamic\Assets\OrderedQueryBuilder;
use Statamic\Contracts\Assets\AssetContainer as AssetContainerContract;
use Statamic\Exceptions\AssetContainerNotFoundException;
use Statamic\Facades\Asset;
use Statamic\Facades\AssetContainer;
use Statamic\Fields\Fieldtype;
use Statamic\Fieldtypes\Assets\DimensionsRule;
use Statamic\Fieldtypes\Assets\ImageRule;
use Statamic\Fieldtypes\Assets\MaxRule;
use Statamic\Fieldtypes\Assets\MimesRule;
use Statamic\Fieldtypes\Assets\MimetypesRule;
use Statamic\Fieldtypes\Assets\MinRule;
use Statamic\Fieldtypes\Assets\UndefinedContainerException;
use Statamic\Support\Arr;
use Statamic\Support\Str;

class FormUpload extends Fieldtype
{
    protected $selectable = false;

    public function preload()
    {
        $values = Arr::wrap($this->field->value());

        return [
            'files' => collect($values)->map(fn ($value) => $this->fileData($value))->values()->all(),
        ];
    }

    private function fileData(string $value): array
    {
        if (! $this->storesAsAsset()) {
            return ['filename' => basename($value)];
        }

        if (! $asset = Asset::find($value)) {
            return ['filename' => $value];
        }

        return [
            'filename' => $asset->basename(),
            'size' => $asset->size(),
            'download_url' => $asset->cpDownloadUrl(),
        ];
    }

    public function preProcess($values)
    {
        if (! $this->storesAsAsset()) {
            return $values ?? [];
        }

        if (is_null($values)) {
            return [];
        }

        return collect($values)->map(fn ($value) => $this->valueToId($value))->filter()->values()->all();
    }

    private function valueToId(string $value): string
    {
        if (Str::contains($value, '::')) {
            return $value;
        }

        return optional($this->container()->asset($value))->id();
    }

    public function process($values)
    {
        if (! $this->storesAsAsset()) {
            return $this->config('max_files') === 1 ? collect($values)->first() : $values;
        }

        // A value is either a fresh upload's asset id (from AssetsUploader), or it's already a
        // stored path from an earlier submission of this page riding along unchanged — e.g. going
        // back and resubmitting a page without re-selecting its file. Only the former needs resolving.
        $values = collect($values)->map(fn ($value) => Str::contains($value, '::') ? Asset::findOrFail($value)->path() : $value);

        return $this->config('max_files') === 1 ? $values->first() : $values->all();
    }

    public function augment($values)
    {
        if (! $this->storesAsAsset()) {
            return $values;
        }

        $values = Arr::wrap($values);

        $ids = collect($values)->map(fn ($value) => $this->container()->handle().'::'.$value)->all();

        $query = new OrderedQueryBuilder($this->container()->queryAssets()->whereIn('path', $values), $ids);

        return $this->config('max_files') === 1 ? $query->first() : $query;
    }

    public function shallowAugment($values)
    {
        if (! $this->storesAsAsset()) {
            return $values;
        }

        $items = $this->augment($values);
        $items = $this->config('max_files') === 1 ? collect([$items]) : $items->get();

        $items = $items->filter()->map(fn ($item) => $item->toShallowAugmentedCollection());

        return $this->config('max_files') === 1 ? $items->first() : $items;
    }

    private function storesAsAsset(): bool
    {
        return (bool) $this->config('store');
    }

    private function container(): ?AssetContainerContract
    {
        if ($configured = $this->config('container')) {
            if ($container = AssetContainer::find($configured)) {
                return $container;
            }

            throw new AssetContainerNotFoundException($configured);
        }

        if (($containers = AssetContainer::all())->count() === 1) {
            return $containers->first();
        }

        throw new UndefinedContainerException;
    }

    public function rules(): array
    {
        $rules = ['array'];

        if ($max = $this->config('max_files')) {
            $rules[] = 'max:'.$max;
        }

        if ($min = $this->config('min_files')) {
            $rules[] = 'min:'.$min;
        }

        return $rules;
    }

    public function fieldRules()
    {
        $classes = [
            'dimensions' => DimensionsRule::class,
            'image' => ImageRule::class,
            'max_filesize' => MaxRule::class,
            'mimes' => MimesRule::class,
            'mimetypes' => MimetypesRule::class,
            'min_filesize' => MinRule::class,
        ];

        return collect(parent::fieldRules())->map(function ($rule) use ($classes) {
            if (! is_string($rule)) {
                return $rule;
            }

            $name = Str::before($rule, ':');

            if ($class = Arr::get($classes, $name)) {
                $parameters = explode(',', Str::after($rule, ':'));

                return new $class($parameters);
            }

            return $rule;
        })->all();
    }
}
