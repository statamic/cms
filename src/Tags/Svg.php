<?php

namespace Statamic\Tags;

use Stringy\StaticStringy;
use Statamic\Facades\File;
use Statamic\Support\Str;
use Statamic\Facades\URL;

class Svg extends Tags
{
    public function index()
    {
        $name = Str::ensureRight($this->params->get('src'), '.svg');

        $cascade = [
            resource_path('svg'),
            resource_path(),
            public_path('svg'),
            public_path(),
        ];

        $svg = null;

        foreach ($cascade as $location) {
            $file = Url::assemble($location, $name);
            if (File::exists($file)) {
                $svg = StaticStringy::collapseWhitespace(
                    File::get($file)
                );
                break;
            }
        }

        return str_replace(
            '<svg',
            sprintf('<svg%s', $this->renderAttributes()),
            $svg
        );
    }

    private function renderAttributes()
    {
        $attrs = collect($this->params->all())->except('src')->all();

        if (count($attrs) == 0) {
            return '';
        }

        return ' '.collect($attrs)->map(function ($value, $attr) {
            if (is_int($attr)) {
                return $value;
            }

            return sprintf('%s="%s"', $attr, $value);
        })->implode(' ');
    }
}
