<?php

namespace Statamic\Tags;

use Rhukster\DomSanitizer\DOMSanitizer;
use Statamic\Facades\File;
use Statamic\Facades\Path;
use Statamic\Fieldtypes\Icon;
use Statamic\Support\Str;
use Stringy\StaticStringy;

class Svg extends Tags
{
    use Concerns\RendersAttributes;

    private static $shouldSanitize = true;

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
            statamic_path('resources/svg/icons'),
        ];

        $svg = null;

        foreach ($cascade as $location) {
            $file = Path::assemble($location, $name);
            if (File::exists($file)) {
                $svg = StaticStringy::collapseWhitespace(
                    File::get($file)
                );
                break;
            }
        }

        if (! $svg && Str::startsWith(mb_strtolower(trim($name)), '<svg')) {
            $svg = $this->params->get('src');
        }

        if (empty($svg)) {
            return '';
        }

        if ($this->params->get('title') || $this->params->get('desc')) {
            $svg = $this->setTitleAndDesc($svg);
        }

        $svg = $this->sanitize($svg);

        $attributes = $this->renderAttributesFromParams(except: ['src', 'title', 'desc', 'sanitize', 'allow_tags', 'allow_attrs']);

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

    private function sanitize($svg)
    {
        if ($this->params->bool('sanitize', static::$shouldSanitize) === false) {
            return $svg;
        }

        $sanitizer = new DOMSanitizer(DOMSanitizer::SVG);
        $this->setAllowedAttrs($sanitizer);
        $this->setAllowedTags($sanitizer);

        return $sanitizer->sanitize($svg, [
            'remove-xml-tags' => ! Str::startsWith($svg, '<?xml'),
        ]);
    }

    private function setAllowedAttrs(DOMSanitizer $sanitizer)
    {
        $attrs = $this->params->explode('allow_attrs', []);
        $allowed = array_merge($sanitizer->getAllowedAttributes(), $attrs);
        $sanitizer->setAllowedAttributes($allowed);
    }

    private function setAllowedTags(DOMSanitizer $sanitizer)
    {
        // The sanitizer package has certain svg tags explicitly disallowed.
        // If we allow them, we need to remove them from the disallowed list.
        // They are defined in lowercase, so we'll lowercase our tags too.
        $tags = collect($this->params->explode('allow_tags', []))->map(fn ($tag) => strtolower($tag))->all();
        $allowed = array_merge($sanitizer->getAllowedTags(), $tags);
        $disallowed = array_diff($sanitizer->getDisallowedTags(), $tags);
        $sanitizer->setAllowedTags($allowed);
        $sanitizer->setDisallowedTags($disallowed);
    }

    public static function disableSanitization()
    {
        static::$shouldSanitize = false;
    }

    public static function enableSanitization()
    {
        static::$shouldSanitize = true;
    }
}
