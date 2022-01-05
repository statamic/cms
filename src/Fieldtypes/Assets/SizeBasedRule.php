<?php

namespace Statamic\Fieldtypes\Assets;

use Illuminate\Contracts\Validation\Rule;
use Statamic\Facades\Asset;
use Symfony\Component\HttpFoundation\File\UploadedFile;

abstract class SizeBasedRule implements Rule
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
            if (($size = $this->getFileSize($id)) === false) {
                return false;
            }

            return $this->sizePasses($size);
        });
    }

    /**
     * Determine if the the rule passes for the given size.
     *
     * @param  int  $size
     * @return bool
     */
    abstract public function sizePasses($size);

    /**
     * Get the validation error message.
     *
     * @return string
     */
    abstract public function message();

    /**
     * Get the file size.
     *
     * @param  string|UploadedFile  $id
     * @return int|false
     */
    protected function getFileSize($id)
    {
        if ($id instanceof UploadedFile) {
            return $id->getSize() / 1024;
        }

        if ($asset = Asset::find($id)) {
            return $asset->size() / 1024;
        }

        return false;
    }
}
