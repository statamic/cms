<?php

namespace Statamic\Addons\Suggest\Modes;

class OptionsMode extends AbstractMode
{
    public function suggestions()
    {
        return $this->request->input('options');
    }
}
