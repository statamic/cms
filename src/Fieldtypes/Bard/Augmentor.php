<?php

namespace Statamic\Fieldtypes\Bard;

use Closure;
use Statamic\Fields\Field;
use Statamic\Fields\Value;
use Statamic\Fields\Values;
use Statamic\Fieldtypes\Text;
use Statamic\Support\Arr;
use Tiptap\Editor;

class Augmentor
{
    protected $fieldtype;
    protected $sets = [];
    protected $includeDisabledSets = false;
    protected $augmentSets = true;
    protected $withStatamicImageUrls = false;
    protected static $extensions = [];
    protected static $extensionReplacements = [];

    public function __construct($fieldtype)
    {
        $this->fieldtype = $fieldtype;
    }

    public function withStatamicImageUrls()
    {
        $this->withStatamicImageUrls = true;

        return $this;
    }

    public function augment($value, $shallow = false)
    {
        $hasSets = (bool) $this->fieldtype->config('sets');

        if (! $value) {
            return $hasSets ? [] : null;
        }

        if (is_string($value)) {
            return $value;
        }

        if (! $hasSets) {
            return $this->convertToHtml($value);
        }

        if (! $this->includeDisabledSets) {
            $value = $this->removeDisabledSets($value);
        }

        $value = $this->addSetIndexes($value);
        $value = $this->convertToHtml($value);
        $value = $this->convertToSets($value);

        if ($this->augmentSets) {
            $value = $this->augmentSets($value, $shallow);
        }

        return collect($value)->mapInto(Values::class)->all();
    }

    public function withDisabledSets()
    {
        $this->includeDisabledSets = true;

        return $this;
    }

    public function withoutAugmentingSets()
    {
        $this->augmentSets = false;

        return $this;
    }

    protected function removeDisabledSets($value)
    {
        return collect($value)->reject(function ($value) {
            return $value['type'] === 'set'
                && Arr::get($value, 'attrs.enabled', true) === false;
        })->values();
    }

    protected function addSetIndexes($value)
    {
        return collect($value)->map(function ($value, $index) {
            if ($value['type'] == 'set') {
                $this->sets[$index] = $value['attrs']['values'];
                $value['index'] = 'index-'.$index;
            }

            return $value;
        })->all();
    }

    public function convertToHtml($value)
    {
        return $this->renderProsemirrorToHtml(['type' => 'doc', 'content' => $value]);
    }

    public static function addExtensions($extensions)
    {
        foreach ($extensions as $name => $extension) {
            static::addExtension($name, $extension);
        }
    }

    public static function addExtension($name, $extension)
    {
        static::$extensions[$name] = $extension;
    }

    public static function replaceExtension($name, $extension)
    {
        static::$extensionReplacements[$name] = $extension;
    }

    protected function convertToSets($html)
    {
        $arr = preg_split('/(<set>index-\d+<\/set>)/', $html, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);

        return collect($arr)->map(function ($html) {
            if (preg_match('/^<set>index-(\d+)<\/set>/', $html, $matches)) {
                return $this->sets[$matches[1]];
            }

            return ['type' => 'text', 'text' => $this->textValue($html)];
        });
    }

    protected function textValue($value)
    {
        $fieldtype = (new Text)->setField(new Field('text', [
            'antlers' => $this->fieldtype->config('antlers'),
        ]));

        return new Value($value, 'text', $fieldtype);
    }

    protected function augmentSets($value, $shallow)
    {
        $augmentMethod = $shallow ? 'shallowAugment' : 'augment';

        return $value->map(function ($set) use ($augmentMethod) {
            if (! $this->fieldtype->config("sets.{$set['type']}.fields")) {
                return $set;
            }

            $values = $this->fieldtype->fields($set['type'])->addValues($set)->{$augmentMethod}()->values()->all();

            return array_merge($values, ['type' => $set['type']]);
        })->all();
    }

    public function renderHtmlToProsemirror(string $value)
    {
        return (new Editor(['extensions' => $this->extensions()]))->setContent($value)->getDocument();
    }

    public function renderProsemirrorToHtml(array $value)
    {
        return (new Editor(['extensions' => $this->extensions()]))->setContent($value)->getHTML();
    }

    public function extensions()
    {
        $extensions = [];
        $options = ['withStatamicImageUrls' => $this->withStatamicImageUrls];

        foreach (static::$extensions as $name => $extension) {
            $extensions[$name] = $extension instanceof Closure
                ? $extension($this->fieldtype, $options)
                : $extension;
        }
        foreach (static::$extensionReplacements as $name => $extension) {
            $extensions[$name] = $extension instanceof Closure
                ? $extension($extensions[$name] ?? null, $this->fieldtype, $options)
                : $extension;
        }

        return Arr::removeNullValues($extensions);
    }
}
