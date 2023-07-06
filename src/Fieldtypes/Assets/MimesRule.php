<?php

namespace Statamic\Fieldtypes\Assets;

use Illuminate\Contracts\Validation\Rule;
use Statamic\Facades\Asset;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class MimesRule implements Rule
{
    protected $parameters;

    public function __construct($parameters)
    {
        if (in_array('jpg', $parameters) || in_array('jpeg', $parameters)) {
            $parameters = array_unique(array_merge($parameters, ['jpg', 'jpeg']));
        }

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
                return in_array($id->guessExtension(), $this->parameters);
            }

            if (! $asset = Asset::find($id)) {
                return false;
            }

            return $asset->guessedExtensionIsOneOf($this->parameters);
        });
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return str_replace(':values', implode(', ', $this->parameters), __('statamic::validation.mimes'));
    }
}
