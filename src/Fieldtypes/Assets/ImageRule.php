<?php

namespace Statamic\Fieldtypes\Assets;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Statamic\Facades\Asset;
use Statamic\Statamic;
use Statamic\Tags\In;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ImageRule implements ValidationRule
{
    public function __construct(protected $parameters) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $extension = '';
        $extensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'svg', 'webp', 'avif'];

        if ($value instanceof UploadedFile) {
            $extension = $value->guessExtension();
        } else if ($asset = Asset::find($value)) {
            $extension = $asset->extension();
        }

        if (!in_array($extension, $extensions)) {
            $fail(__((Statamic::isCpRoute() ? 'statamic::' : '') . 'validation.image', ['extensions' => implode(', ', $extensions)]));
        }
    }
}
