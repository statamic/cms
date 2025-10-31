<?php

namespace Statamic\Fieldtypes\Assets;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Statamic\Contracts\GraphQL\CastableToValidationString;
use Statamic\Facades\Asset;
use Statamic\Statamic;
use Stringable;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class MimetypesRule implements CastableToValidationString, Stringable, ValidationRule
{
    public function __construct(protected $parameters)
    {
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $mime_type = '';

        if ($value instanceof UploadedFile) {
            $mime_type = $value->getMimeType();
        } elseif ($asset = Asset::find($value)) {
            $mime_type = $asset->mimeType();
        }

        if (! in_array($mime_type, $this->parameters) && ! in_array(explode('/', $mime_type)[0].'/*', $this->parameters)) {
            $fail($this->message());
        }
    }

    public function message()
    {
        return __((Statamic::isCpRoute() ? 'statamic::' : '').'validation.mimetypes', ['values' => implode(', ', $this->parameters)]);
    }

    public function __toString()
    {
        return 'mimetypes:'.implode(',', $this->parameters);
    }

    public function toGqlValidationString(): string
    {
        return $this->__toString();
    }
}
