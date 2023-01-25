<?php

namespace Statamic\Tags;

use Statamic\Facades\File;
use Statamic\Facades\URL;
use Statamic\Support\Str;
use Stringy\StaticStringy;

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

        $attributes = $this->renderAttributesFromParams(['src', 'title', 'desc']);

        if ($this->params->get('title') || $this->params->get('desc')) {
            $svg = $this->setTitleAndDesc($svg);
        }

        return str_replace(
            '<svg',
            collect(['<svg', $attributes])->filter()->implode(' '),
            $svg
        );
    }

    private function setTitleAndDesc($svg)
    {
        $doc = new \DOMDocument;
        $doc->loadXML($svg);

        if ($desc = $this->params->get('desc')) {
            if ($el = $doc->getElementsByTagName('desc')[0]) {
                $el->nodeValue = $desc;
            } else {
                $el = $doc->createElement('desc', $desc);
                $doc->firstChild->insertBefore($el, $doc->firstChild->firstChild);
            }
        }

        if ($title = $this->params->get('title')) {
            if ($el = $doc->getElementsByTagName('title')[0]) {
                $el->nodeValue = $title;
            } else {
                $el = $doc->createElement('title', $title);
                $doc->firstChild->insertBefore($el, $doc->firstChild->firstChild);
            }
        }

        return $doc->saveHTML();
    }
}
