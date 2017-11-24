<?php

namespace Statamic\Addons\Suggest;

interface Mode
{
    /**
     * Get the suggestions.
     *
     * This should return an associative array with each key being an array containing `value` and `text` keys.
     * For example: return [
     *   ['value' => 'foo', 'text' => 'Foo'],
     *   ['value' => 'bar', 'text' => 'Bar']
     * ];
     *
     * @return array
     */
    public function suggestions();
}
