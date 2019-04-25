<?php

namespace Statamic\Fields\Fieldtypes\Bard;

use Statamic\API\Arr;
use Scrumpy\ProseMirrorToHtml\Renderer;

class Augmentor
{
    protected $sets = [];
    protected $includeDisabledSets = false;

    public function augment($value)
    {
        if (is_string($value)) {
            return $value;
        }

        if (!$this->includeDisabledSets) {
            $value = $this->removeDisabledSets($value);
        }

        $value = $this->addSetIndexes($value);
        $value = $this->convertToHtml($value);
        $value = $this->convertToSets($value);

        return $value;
    }

    public function withDisabledSets()
    {
        $this->includeDisabledSets = true;

        return $this;
    }

    protected function removeDisabledSets($value)
    {
        return collect($value)->reject(function ($value) {
            return $value['type'] === 'set'
                && Arr::get($value, 'attrs.enabled', true) === false;
        });
    }

    protected function addSetIndexes($value)
    {
        return collect($value)->map(function ($value, $index) {
            if ($value['type'] == 'set') {
                $this->sets[$index] = $value['attrs']['values'];
                $value['index'] = $index;
            }

            return $value;
        })->all();
    }

    public function convertToHtml($value)
    {
        $renderer = new Renderer;
        $renderer->addNodes([ImageNode::class, SetNode::class]);

        return $renderer->render([
            'type' => 'doc',
            'content' => $value
        ]);
    }

    protected function convertToSets($html)
    {
        $arr = preg_split('/(<set>\d+<\/set>)/', $html, -1, PREG_SPLIT_DELIM_CAPTURE);

        return collect($arr)->map(function ($html) {
            if (preg_match('/^<set>(\d+)<\/set>/', $html, $matches)) {
                return $this->sets[$matches[1]];
            }

            return ['type' => 'text', 'text' => $html];
        })->all();
    }
}
