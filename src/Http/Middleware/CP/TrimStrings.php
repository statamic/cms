<?php

namespace Statamic\Http\Middleware\CP;

use Illuminate\Foundation\Http\Middleware\TransformsRequest;
use Statamic\Support\Str;

class TrimStrings extends TransformsRequest
{
    /**
     * The attributes that should not be trimmed.
     *
     * @var array<int, string>
     */
    protected $except = [
        //
    ];

    /**
     * Clean the data in the given array.
     *
     * @param  array  $data
     * @param  string  $keyPrefix
     * @return array
     */
    protected function cleanArray(array $data, $keyPrefix = '')
    {
        if ($this->isBardTextNode($data, $keyPrefix)) {
            $this->except[] = $keyPrefix.'text';
        }

        return parent::cleanArray($data, $keyPrefix);
    }

    /**
     * Check if the data is a bard text node.
     *
     * @param  array  $data
     * @return array
     */
    protected function isBardTextNode(array $data, $keyPrefix = '')
    {
        return
            array_key_exists('text', $data) &&
            array_key_exists('type', $data) &&
            is_string($data['text']) &&
            $data['type'] === 'text' &&
            Str::is('*.content.*', $keyPrefix);
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

        return preg_replace('~^[\s\x{FEFF}\x{200B}]+|[\s\x{FEFF}\x{200B}]+$~u', '', $value) ?? trim($value);
    }
}
