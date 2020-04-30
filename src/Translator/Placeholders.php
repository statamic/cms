<?php

namespace Statamic\Translator;

class Placeholders
{
    public function wrap($text)
    {
        return preg_replace_callback('/:\w+/', function ($m) {
            return "<span class=\"notranslate\">{$m[0]}</span>";
        }, $text);
    }

    public function unwrap($text)
    {
        return preg_replace_callback('/<span class=\"notranslate\">(:\w+)<\/span>/', function ($m) {
            return $m[1];
        }, $text);
    }
}
