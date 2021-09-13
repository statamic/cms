<?php

namespace Statamic\View\Antlers\Language\Runtime\Libraries\Internal;

use Statamic\View\Antlers\Language\Runtime\Libraries\RuntimeLibrary;

class ScopeLibrary extends RuntimeLibrary
{
    protected $name = 'scope';
    protected $isRuntimeProtected = true;

    public function __construct()
    {
        $this->exposedMethods = [
            'copy' => 1,
        ];
    }

    public function copy()
    {
        if ($this->activeEnvironment == null) {
            return [];
        }

        return $this->activeEnvironment->getData();
    }
}
