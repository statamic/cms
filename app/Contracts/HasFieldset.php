<?php

namespace Statamic\Contracts;

interface HasFieldset
{
    /**
     * Get or set the fieldset
     *
     * @param string|null|bool
     * @return \Statamic\Contracts\Fields\Fieldset
     */
    public function fieldset($fieldset = null);
}
