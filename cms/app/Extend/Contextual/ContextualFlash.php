<?php

namespace Statamic\Extend\Contextual;

class ContextualFlash extends ContextualSession
{
    /**
     * Save a key to the flash
     *
     * @param string $key   Key to save under
     * @param mixed  $data  Data to store
     */
    public function put($key, $data = null)
    {
        session()->flash($this->contextualize($key), $data);
    }
}
