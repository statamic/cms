<?php

namespace Statamic\Fieldtypes\Assets;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Statamic\Facades\Asset;
use Statamic\Statamic;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class MimesRule implements ValidationRule
{
    protected $parameters;

    public function __construct($parameters)
    {
        if (in_array('jpg', $parameters) || in_array('jpeg', $parameters)) {
            $parameters = array_unique(array_merge($parameters, ['jpg', 'jpeg']));
        }

        $this->parameters = array_map(strtolower(...), $parameters);
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $mime = '';

        if ($value instanceof UploadedFile) {
            $mime = $value->guessExtension();
        } elseif ($asset = Asset::find($value)) {
            $mime = $asset->extension();
        }

        if (! in_array($mime, $this->parameters)) {
            $fail($this->message());
        }
    }

    public function message(): string
    {
        return __((Statamic::isCpRoute() ? 'statamic::' : '').'validation.mimes', ['values' => implode(', ', $this->parameters)]);
    }
}
