<?php

namespace Statamic\Fieldtypes\Assets;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Statamic\Contracts\GraphQL\CastableToValidationString;
use Statamic\Facades\Asset;
use Statamic\Statamic;
use Stringable;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class MaxRule implements CastableToValidationString, Stringable, ValidationRule
{
    public function __construct(protected $parameters)
    {
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $size = 0;

        if ($value instanceof UploadedFile) {
            $size = $value->getSize() / 1024;
        } elseif ($asset = Asset::find($value)) {
            $size = $asset->size() / 1024;
        }

        if ($size > $this->parameters[0]) {
            $fail($this->message());
        }
    }

    public function message(): string
    {
        return __((Statamic::isCpRoute() ? 'statamic::' : '').'validation.max.file', ['max' => $this->parameters[0]]);
    }

    public function __toString(): string
    {
        return 'max_filesize:'.$this->parameters[0];
    }

    public function toGqlValidationString(): string
    {
        return $this->__toString();
    }
}
