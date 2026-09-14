<?php

namespace Tests\Antlers\Fixtures\Components;

use Illuminate\View\Component;

class Card extends Component
{
    public function __construct(
        public string $title,
    ) {

    }

    public function render()
    {
        return view('components.card');
    }
}
