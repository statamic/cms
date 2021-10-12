<?php

namespace Statamic\CP\Toasts;

class ToastsHolder
{
    /**
     * @var Toast[]
     */
    private $toasts = [];

    public function push(Toast $toast)
    {
        array_push($this->toasts, $toast);
    }

    /**
     * @return Toast[]
     */
    public function all(): array
    {
        return $this->toasts;
    }
}
