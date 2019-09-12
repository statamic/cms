<?php

namespace Statamic\Tags;

use Statamic\Support\Html;
use Statamic\Tags\Tags;

class Obfuscate extends Tags
{
    public function index()
    {
        return Html::obfuscate($this->content);
    }
}
