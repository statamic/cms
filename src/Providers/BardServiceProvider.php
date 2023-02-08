<?php

namespace Statamic\Providers;

use Illuminate\Support\ServiceProvider;
use Statamic\Fieldtypes\Bard\Augmentor;

class BardServiceProvider extends ServiceProvider
{
    public function boot()
    {
        Augmentor::addExtensions([
            'blockquote' => new \Tiptap\Nodes\Blockquote(),
            'bold' => new \Tiptap\Marks\Bold(),
            'bulletList' => new \Tiptap\Nodes\BulletList(),
            'code' => new \Tiptap\Marks\Code(),
            'codeBlock' => new \Tiptap\Nodes\CodeBlock(),
            'document' => new \Tiptap\Nodes\Document(),
            'hardBreak' => new \Tiptap\Nodes\HardBreak(),
            'heading' => new \Tiptap\Nodes\Heading(),
            'horizontalRule' => new \Tiptap\Nodes\HorizontalRule(),
            'image' => function ($bard, $options) {
                return $options['withStatamicImageUrls']
                    ? new \Statamic\Fieldtypes\Bard\StatamicImageNode
                    : new \Statamic\Fieldtypes\Bard\ImageNode;
            },
            'italic' => new \Tiptap\Marks\Italic(),
            'link' => function ($bard, $options) {
                return $options['withStatamicImageUrls']
                    ? new \Statamic\Fieldtypes\Bard\StatamicLinkMark
                    : new \Statamic\Fieldtypes\Bard\LinkMark;
            },
            'listItem' => new \Tiptap\Nodes\ListItem(),
            'orderedList' => new \Tiptap\Nodes\OrderedList(),
            'paragraph' => new \Tiptap\Nodes\Paragraph(),
            'set' => new \Statamic\Fieldtypes\Bard\SetNode(),
            'subscript' => new \Tiptap\Marks\Subscript(),
            'superscript' => new \Tiptap\Marks\Superscript(),
            'small' => new \Statamic\Fieldtypes\Bard\Marks\Small(),
            'strike' => new \Tiptap\Marks\Strike(),
            'table' => new \Tiptap\Nodes\Table(),
            'tableCell' => new \Tiptap\Nodes\TableCell(),
            'tableHeader' => new \Tiptap\Nodes\TableHeader(),
            'tableRow' => new \Tiptap\Nodes\TableRow(),
            'text' => new \Tiptap\Nodes\Text(),
            'textAlign' => new \Tiptap\Extensions\TextAlign(['types' => ['heading', 'paragraph']]),
            'underline' => new \Tiptap\Marks\Underline(),
        ]);
    }
}
