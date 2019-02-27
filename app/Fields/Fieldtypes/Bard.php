<?php

namespace Statamic\Fields\Fieldtypes;

class Bard extends Replicator
{
    public $category = ['text', 'structured'];

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
        } else {
            if ($this->usesMarkdown()) {
                $data = $this->initializeWithMarkdown($data);
            }
        }

        return $data;
    }

    public function process($data)
    {
        $data = parent::process($data);

        if (empty($this->config('sets'))) {
            $data = $data[0]['text'];

            if ($data === '<p><br></p>') {
                $data = null;
            }
        }

        // Prevent empty fields from saving an empty paragraph.
        if ($this->isEmpty($data)) {
            return null;
        }

        if (is_array($data) && $this->usesMarkdown()) {
            foreach ($data as &$block) {
                if ($block['type'] === 'text') {
                    $block['text'] = $this->contentEditableHtmlToMarkdown($block['text']);
                }
            }
        }

        return $data;
    }

    private function usesMarkdown()
    {
        return $this->config('markdown');
    }

    private function isEmpty($data)
    {
        if (is_array($data) && count($data) === 1 && $data[0]['type'] === 'text') {
            // If the "text" key doesn't exist at all, the user may have
            // manually cleared out the contents using the source view.
            if (! array_has($data[0], 'text')) {
                return true;
            }

            return $data[0]['text'] === '<p><br></p>';
        }

        return false;
    }

    private function initializeWithMarkdown($data)
    {
        foreach ($data as &$block) {
            if ($block['type'] === 'text') {
                $block['text'] = $this->markdownToContentEditableHtml($block['text']);
            }
        }

        return $data;
    }

    private function contentEditableHtmlToMarkdown($text)
    {
        // In Markdown, a line break is indicated with two trailing spaces at the end of the line. In Bard, you don't
        // need the spaces, you only need to shift+enter. We'll account for users that add those trailing spaces
        // anyway. Note that the trailing spaces will get stripped off from the YAML anyway until this issue
        // is resolved: https://github.com/statamic/v2-hub/issues/1324
        $text = str_replace('&nbsp;&nbsp;<br>', "  \n", $text);
        $text = str_replace('<br>', "  \n", $text);

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
