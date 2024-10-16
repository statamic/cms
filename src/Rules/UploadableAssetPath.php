<?php

namespace Statamic\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Statamic\Contracts\Assets\AssetContainer;

class UploadableAssetPath implements ValidationRule
{
    public function __construct(private AssetContainer $container)
    {
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($this->container->asset($value)) {
            $fail('statamic::validation.asset_file_exists')->translate();
        }
    }
}
