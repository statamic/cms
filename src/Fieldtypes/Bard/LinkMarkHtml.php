<?php

namespace Statamic\Fieldtypes\Bard;

use HtmlToProseMirror\Marks\Link;

class LinkMarkHtml extends Link
{
    public function data()
    {
        $data = parent::data();

        if ($title = $this->DOMNode->getAttribute('title')) {
            $data['attrs']['title'] = $title;
        }

        return $data;
    }
}
