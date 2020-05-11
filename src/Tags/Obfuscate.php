<?php

namespace Statamic\Tags;

use Statamic\Support\Html;

class Obfuscate extends Tags
{
    public function index()
    {
        return Html::obfuscate($this->content);
    }
}
