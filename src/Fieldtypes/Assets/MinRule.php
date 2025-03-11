<?php

namespace Statamic\Fieldtypes\Assets;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Statamic\Facades\Asset;
use Statamic\Statamic;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class MinRule implements ValidationRule
{
    public function __construct(protected $parameters) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $size = 0;

        if ($value instanceof UploadedFile) {
            $size = $value->getSize() / 1024;
        } else if ($asset = Asset::find($value)) {
            $size = $asset->size() / 1024;
        }

        if ($size < $this->parameters[0]) {
            $fail(__((Statamic::isCpRoute() ? 'statamic::' : '') . 'validation.min.file', ['min' => $this->parameters[0]]));
        }
    }
}
