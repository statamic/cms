<?php

namespace Statamic\Providers;

use Illuminate\Support\ServiceProvider;
use Statamic\Fieldtypes\Bard\Augmentor;

class BardServiceProvider extends ServiceProvider
{
    public function boot()
    {
        Augmentor::addExtensions([
            'bard-set' => new \Statamic\Fieldtypes\Bard\SetNode(),
            'blockquoute' => new \Tiptap\Nodes\Blockquote(),
            'bold' => new \Tiptap\Marks\Bold(),
            'bulletlist' => new \Tiptap\Nodes\BulletList(),
            'code' => new \Tiptap\Marks\Code(),
            'code-block' => new \Tiptap\Nodes\CodeBlock(),
            'document' => new \Tiptap\Nodes\Document(),
            'hard-break' => new \Tiptap\Nodes\HardBreak(),
            'heading' => new \Tiptap\Nodes\Heading(),
            'horizontal-rule' => new \Tiptap\Nodes\HorizontalRule(),
            'italic' => new \Tiptap\Marks\Italic(),
            'link' => new \Statamic\Fieldtypes\Bard\LinkMark(),
            'list-item' => new \Tiptap\Nodes\ListItem(),
            'ordered-list' => new \Tiptap\Nodes\OrderedList(),
            'paragraph' => new \Tiptap\Nodes\Paragraph(),
            'subscript' => new \Tiptap\Marks\Subscript(),
            'superscript' => new \Tiptap\Marks\Superscript(),
            'small' => new \Statamic\Fieldtypes\Bard\Marks\Small(),
            'strike' => new \Tiptap\Marks\Strike(),
            'table' => new \Tiptap\Nodes\Table(),
            'table-cell' => new \Tiptap\Nodes\TableCell(),
            'table-header' => new \Tiptap\Nodes\TableHeader(),
            'table-row' => new \Tiptap\Nodes\TableRow(),
            'text' => new \Tiptap\Nodes\Text(),
        ]);
    }
}
