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
        $extensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'svg', 'webp'];

        return collect($value)->every(function ($id) use ($extensions) {
            if ($id instanceof UploadedFile) {
                return in_array($id->guessExtension(), $extensions);
            }

            if (! $asset = Asset::find($id)) {
                return false;
            }

            return $asset->guessedExtensionIsOneOf($extensions);
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
