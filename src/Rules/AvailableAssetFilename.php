<?php

namespace Statamic\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Statamic\Contracts\Assets\Asset;
use Statamic\Support\Arr;

class AvailableAssetFilename implements ValidationRule
{
    public function __construct(private Asset $asset)
    {
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value === $this->asset->filename()) {
            $fail('This is the current filename.');

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
            ? $fail('A file already exists with this name and has the same content. You may want to delete this rather than rename it.')
            : $fail('A file already exists with this name but has different content. You may want to replace the other file with this one instead.');
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
