<?php

namespace Statamic\Fieldtypes\Bard;

use ProseMirrorToHtml\Nodes\Image as DefaultImageNode;
use ProseMirrorToHtml\Renderer;
use Statamic\Fields\Field;
use Statamic\Fields\Value;
use Statamic\Fieldtypes\Bard\ImageNode as CustomImageNode;
use Statamic\Fieldtypes\Text;
use Statamic\Support\Arr;

class Augmentor
{
    protected $fieldtype;
    protected $sets = [];
    protected $includeDisabledSets = false;
    protected $augmentSets = true;

    protected static $customMarks = [];
    protected static $customNodes = [];

    public function __construct($fieldtype)
    {
        $this->fieldtype = $fieldtype;
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

        return $value;
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
        return (new Renderer)
            ->replaceNode(DefaultImageNode::class, CustomImageNode::class)
            ->addNode(SetNode::class)
            ->addNodes(static::$customNodes)
            ->addMarks(static::$customMarks)
            ->render(['type' => 'doc', 'content' => $value]);
    }

    public static function addNode($node)
    {
        static::$customNodes[] = $node;
    }

    public static function addMark($mark)
    {
        static::$customMarks[] = $mark;
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
}
