<?php

namespace Statamic\Tags;

use Statamic\Facades\Html;
use Statamic\Tags\Tags;

class Obfuscate extends Tags
{
    public function index()
    {
        return Html::obfuscate($this->content);
    }
}
