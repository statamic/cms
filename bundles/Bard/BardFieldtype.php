<?php

namespace Statamic\Addons\Bard;

use Statamic\Addons\Replicator\ReplicatorFieldtype;

class BardFieldtype extends ReplicatorFieldtype
{
    public function preProcess($data)
    {
        if (is_string($data)) {
            if ($data === '') {
                $data = '<p><br></p>';
            }

            $data = [['type' => 'text', 'text' => $data]];
        }

        $data = parent::preProcess($data);

        if (empty($data)) {
            $data = [['type' => 'text', 'text' => '<p><br></p>']];
        }

        if ($this->getFieldConfig('markdown')) {
            foreach ($data as &$block) {
                if ($block['type'] === 'text') {
                    $block['text'] = $this->markdownToContentEditableHtml($block['text']);
                }
            }
        }

        return $data;
    }

    public function process($data)
    {
        $data = parent::process($data);

        if (empty($this->getFieldConfig('sets'))) {
            $data = $data[0]['text'];

            if ($data === '<p><br></p>') {
                $data = null;
            }
        }

        if (is_array($data) && $this->getFieldConfig('markdown')) {
            foreach ($data as &$block) {
                if ($block['type'] === 'text') {
                    $block['text'] = $this->contentEditableHtmlToMarkdown($block['text']);
                }
            }
        }

        return $data;
    }

    private function contentEditableHtmlToMarkdown($text)
    {
        $text = str_replace('</p>', "\n\n", $text);
        $text = str_replace("\n\n\n", "\n\n", $text);
        $text = strip_tags($text);

        return ltrim($text, "\n");
    }

    private function markdownToContentEditableHtml($text)
    {
        return collect(
            explode("\n\n", $text)
        )->map(function ($paragraph) {
            $paragraph = str_replace("\n", '<br>', $paragraph);
            return '<p>' . $paragraph . '</p>';
        })->implode("\n");
    }
}
