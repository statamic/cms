<?php

namespace Statamic\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Statamic\Assets\AssetUploader;
use Statamic\Contracts\Assets\Asset;
use Statamic\Support\Arr;

class AvailableAssetFilename implements ValidationRule
{
    public function __construct(private Asset $asset)
    {
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $value = AssetUploader::getSafeFilename($value);

        if ($value === $this->asset->filename()) {
            $fail('statamic::validation.asset_current_filename')->translate();

            return;
        }

        // Figure out the intended path.
        $path = str($this->asset->path())
            ->replaceEnd($this->asset->basename(), $value.'.'.$this->asset->extension())
            ->toString();

        // Get the asset at the intended path.
        // If there is no file at the intended path. It's valid.
        if (! $existing = $this->asset->container()->asset($path)) {
            return;
        }

        $this->isSameFile($this->asset, $existing)
            ? $fail('statamic::validation.asset_file_exists_same_content')->translate()
            : $fail('statamic::validation.asset_file_exists_different_content')->translate();
    }

    private function isSameFile($a, $b)
    {
        return $this->prepareForComparingFile($a) == $this->prepareForComparingFile($b);
    }

    private function prepareForComparingFile($file)
    {
        return Arr::except($file->meta(), ['data', 'last_modified']);
    }
}
