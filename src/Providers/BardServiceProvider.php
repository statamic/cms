<?php

namespace Statamic\Providers;

use Illuminate\Support\ServiceProvider;
use Statamic\Fieldtypes\Bard\Augmentor;

class BardServiceProvider extends ServiceProvider
{
    public function boot()
    {
        Augmentor::addExtensions([
            'blockquote' => fn () => new \Tiptap\Nodes\Blockquote(),
            'bold' => fn () => new \Tiptap\Marks\Bold(),
            'bulletList' => fn () => new \Tiptap\Nodes\BulletList(),
            'code' => fn () => new \Tiptap\Marks\Code(),
            'codeBlock' => fn () => new \Tiptap\Nodes\CodeBlock(),
            'document' => fn () => new \Tiptap\Nodes\Document(),
            'hardBreak' => fn () => new \Tiptap\Nodes\HardBreak(),
            'heading' => fn () => new \Tiptap\Nodes\Heading(),
            'horizontalRule' => fn () => new \Tiptap\Nodes\HorizontalRule(),
            'image' => function ($bard, $options) {
                return $options['withStatamicImageUrls']
                    ? new \Statamic\Fieldtypes\Bard\StatamicImageNode
                    : new \Statamic\Fieldtypes\Bard\ImageNode;
            },
            'italic' => fn () => new \Tiptap\Marks\Italic(),
            'link' => function ($bard, $options) {
                return $options['withStatamicImageUrls']
                    ? new \Statamic\Fieldtypes\Bard\StatamicLinkMark
                    : new \Statamic\Fieldtypes\Bard\LinkMark;
            },
            'listItem' => fn () => new \Tiptap\Nodes\ListItem(),
            'orderedList' => fn () => new \Tiptap\Nodes\OrderedList(),
            'paragraph' => fn () => new \Tiptap\Nodes\Paragraph(),
            'set' => fn () => new \Statamic\Fieldtypes\Bard\SetNode(),
            'subscript' => fn () => new \Tiptap\Marks\Subscript(),
            'superscript' => fn () => new \Tiptap\Marks\Superscript(),
            'small' => fn () => new \Statamic\Fieldtypes\Bard\Marks\Small(),
            'strike' => fn () => new \Tiptap\Marks\Strike(),
            'table' => fn () => new \Tiptap\Nodes\Table(),
            'tableCell' => fn () => new \Tiptap\Nodes\TableCell(),
            'tableHeader' => fn () => new \Tiptap\Nodes\TableHeader(),
            'tableRow' => fn () => new \Tiptap\Nodes\TableRow(),
            'text' => fn () => new \Tiptap\Nodes\Text(),
            'textAlign' => fn () => new \Tiptap\Extensions\TextAlign(['types' => ['heading', 'paragraph']]),
            'underline' => fn () => new \Tiptap\Marks\Underline(),
        ]);
    }
}
