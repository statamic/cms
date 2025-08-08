<?php

namespace Statamic\Fieldtypes\Assets;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Statamic\Contracts\GraphQL\CastableToValidationString;
use Statamic\Facades\Asset;
use Statamic\Statamic;
use Stringable;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ImageRule implements CastableToValidationString, Stringable, ValidationRule
{
    public $extensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'svg', 'webp', 'avif'];

    public function __construct(protected $parameters)
    {
        if ($this->parameters !== ['image']) {
            $this->extensions = array_map(strtolower(...), $this->parameters);
        }
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $extension = '';

        if ($value instanceof UploadedFile) {
            $extension = $value->guessExtension();
        } elseif ($asset = Asset::find($value)) {
            $extension = $asset->extension();
        }

        if (! in_array($extension, $this->extensions)) {
            $fail($this->message());
        }
    }

    public function message(): string
    {
        return __((Statamic::isCpRoute() ? 'statamic::' : '').'validation.image', ['extensions' => implode(', ', $this->extensions)]);
    }

    public function __toString()
    {
        return 'image:'.implode(',', $this->extensions);
    }

    public function toGqlValidationString(): string
    {
        return $this->__toString();
    }
}
