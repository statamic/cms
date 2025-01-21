<?php

namespace Statamic\Http\Middleware\CP;

use Illuminate\Foundation\Http\Middleware\TransformsRequest;
use Illuminate\Support\Str;

class TrimStrings extends TransformsRequest
{
    /**
     * The attributes that should not be trimmed.
     *
     * @var array<int, string>
     */
    protected $except = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Clean the data in the given array.
     *
     * @param  string  $keyPrefix
     * @return array
     */
    protected function cleanArray(array $data, $keyPrefix = '')
    {
        if ($this->isTextNode($data)) {
            $this->except[] = $keyPrefix.'text';
        }

        return parent::cleanArray($data, $keyPrefix);
    }

    /**
     * Check if the data is a Bard (ProseMirror) text node.
     */
    private function isTextNode(array $data): bool
    {
        return array_key_exists('text', $data)
            && array_key_exists('type', $data)
            && is_string($data['text'])
            && $data['type'] === 'text';
    }

    /**
     * Transform the given value.
     *
     * @param  string  $key
     * @param  mixed  $value
     * @return mixed
     */
    protected function transform($key, $value)
    {
        if (in_array($key, $this->except, true) || ! is_string($value)) {
            return $value;
        }

        // This is copied from Str::trim() which was only added to Laravel in 11.2.0.
        // See https://github.com/laravel/framework/pull/50822
        // Once our min requirement goes beyond that, we can remove this guard.
        if (! method_exists(Str::class, 'trim')) {
            return preg_replace('~^[\s\x{FEFF}\x{200B}]+|[\s\x{FEFF}\x{200B}]+$~u', '', $value) ?? trim($value);
        }

        return Str::trim($value);
    }
}
