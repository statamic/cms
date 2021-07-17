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

    protected static $rules = [
        'image',
        'dimensions',
        'mimes',
        'mimetypes',
    ];

    public function __construct($name, $parameters = null)
    {
        $this->name = $name;
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
        $key = 'statamic::validation.'.$this->name;

        if (in_array($this->name, ['mimes', 'mimetypes'])) {
            $replace = ['values' => join(', ', $this->parameters)];
        }

        return __($key, $replace ?? []);
    }

    /**
     * Make an AssetRule instance if it's a supported rule, or return what was passed in.
     *
     * @return self|string
     */
    public static function makeFromRule($rule)
    {
        $name = Str::before($rule, ':');

        if (! in_array($name, static::$rules)) {
            return $rule;
        }

        $parameters = Str::contains($rule, ':')
            ? explode(',', Str::after($rule, ':'))
            : null;

        return new AssetRule($name, $parameters);
    }
}
