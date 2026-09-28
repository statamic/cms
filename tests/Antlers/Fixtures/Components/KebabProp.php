<?php

namespace Tests\Antlers\Fixtures\Components;

use Illuminate\View\Component;

class KebabProp extends Component
{
    public function __construct(
        public ?string $someProp = null,
    ) {

    }

    public function render()
    {
        return view('components.kebab_prop_class');
    }
}
