<?php

namespace Statamic\Tags;

use Statamic\Tags\Concerns;
use Stringy\StaticStringy;
use Statamic\Facades\File;
use Statamic\Support\Str;
use Statamic\Facades\URL;

class Svg extends Tags
{
    use Concerns\RendersAttributes;

    public function wildcard($src)
    {
        $this->params['src'] = $src;

        return $this->index();
    }

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

        $attributes = $this->renderAttributes(['src']);

        return str_replace(
            '<svg',
            collect(['<svg', $attributes])->filter()->implode(' '),
            $svg
        );
    }
}
