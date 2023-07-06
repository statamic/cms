<?php

namespace Statamic\Fieldtypes\Assets;

use Illuminate\Contracts\Validation\Rule;
use Statamic\Facades\Asset;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class MimetypesRule implements Rule
{
    protected $parameters;

    public function __construct($parameters)
    {
        $this->parameters = $parameters;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        return collect($value)->every(function ($id) {
            if ($id instanceof UploadedFile) {
                $mimeType = $id->getMimeType();
            } elseif (! ($mimeType = optional(Asset::find($id))->mimeType())) {
                return false;
            }

            return in_array($mimeType, $this->parameters) ||
                in_array(explode('/', $mimeType)[0].'/*', $this->parameters);
        });
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return str_replace(':values', implode(', ', $this->parameters), __('statamic::validation.mimetypes'));
    }
}
