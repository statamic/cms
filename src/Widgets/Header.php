<?php

namespace Statamic\Widgets;

class Header extends Widget
{
    public function component()
    {
        $classes = $this->config('classes', 'w-full');
        $text = $this->config('text');

        return VueComponent::render('dynamic-html-renderer', [
            'html' => "<h2 class=\"{$classes}\">{$text}</h2>",
        ]);
    }
}
