<?php

namespace Statamic\Contracts;

interface HasFieldset
{
    /**
     * Get or set the fieldset
     *
     * @param string|null|bool
     * @return \Statamic\Contracts\CP\Fieldset
     */
    public function fieldset($fieldset = null);
}
