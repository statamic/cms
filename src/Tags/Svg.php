<?php

namespace Statamic\Tags;

use enshrined\svgSanitize\data\AllowedAttributes;
use enshrined\svgSanitize\data\AllowedTags;
use enshrined\svgSanitize\Sanitizer;
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

        $svg = str_replace(
            '<svg',
            collect(['<svg', $attributes])->filter()->implode(' '),
            $svg
        );

        return $this->sanitize($svg);
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

    private function sanitize($svg)
    {
        if ($this->params->bool('sanitize') === false) {
            return $svg;
        }

        $sanitizer = new Sanitizer;
        $sanitizer->removeXMLTag(! Str::startsWith($svg, '<?xml'));
        $sanitizer->setAllowedAttrs($this->getAllowedAttrs());
        $sanitizer->setAllowedTags($this->getAllowedTags());

        return $sanitizer->sanitize($svg);
    }

    private function getAllowedAttrs()
    {
        $attrs = $this->params->explode('allow_attrs', []);

        return new class($attrs) extends AllowedAttributes
        {
            private static $attrs = [];

            public function __construct($attrs)
            {
                self::$attrs = $attrs;
            }

            public static function getAttributes()
            {
                return array_merge(parent::getAttributes(), self::$attrs);
            }
        };
    }

    private function getAllowedTags()
    {
        $tags = $this->params->explode('allow_tags', []);

        return new class($tags) extends AllowedTags
        {
            private static $tags = [];

            public function __construct($tags)
            {
                self::$tags = $tags;
            }

            public static function getTags()
            {
                return array_merge(parent::getTags(), self::$tags);
            }
        };
    }
}
