<?php

namespace Statamic\Fieldtypes\Assets;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Validation\Concerns\ValidatesAttributes;
use Statamic\Facades\Asset;
use Statamic\Support\Str;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class AssetRule implements Rule
{
    use ValidatesAttributes;

    protected $name;
    protected $parameters;
    protected $message;

    public function __construct($name, $message, $parameters = null)
    {
        $this->name = $name;
        $this->message = $message;
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
        $method = 'validate'.Str::studly($this->name);

        return collect($value)->every(function ($file) use ($attribute, $method) {
            if (! ($file instanceof UploadedFile)) {
                if (! $asset = Asset::find($file)) {
                    return false;
                }

                $file = new File($asset->resolvedPath());
            }

            return $this->{$method}($attribute, $file, $this->parameters);
        });
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return $this->message;
    }
}
