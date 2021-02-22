<?php

namespace Statamic\Fieldtypes\Assets;

use Illuminate\Contracts\Validation\Rule;
use Statamic\Facades\Asset;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ImageRule implements Rule
{
    protected $parameters;

    public function __construct($parameters = null)
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
                return in_array($id->guessExtension(), ['jpg', 'jpeg', 'png', 'gif', 'webp']);
            }

            if (! $asset = Asset::find($id)) {
                return false;
            }

            return $asset->isImage();
        });
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return __('statamic::validation.image');
    }
}
