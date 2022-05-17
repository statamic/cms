<?php

namespace Statamic\Providers;

use Tiptap\Nodes\Table;
use Tiptap\Nodes\TableRow;
use Tiptap\Marks\Subscript;
use Tiptap\Nodes\TableCell;
use Tiptap\Marks\Superscript;
use Tiptap\Nodes\TableHeader;
use Tiptap\Extensions\StarterKit;
use Statamic\Fieldtypes\Bard\SetNode;
use Statamic\Fieldtypes\Bard\LinkMark;
use Illuminate\Support\ServiceProvider;
use Statamic\Fieldtypes\Bard\Augmentor;
use Statamic\Fieldtypes\Bard\Marks\Small;

class BardServiceProvider extends ServiceProvider
{
    public function boot()
    {
        Augmentor::addExtensions([
            'link' => new LinkMark(),
            'bard-set' => new SetNode(),
            'starterkit' => new StarterKit(),
            'subscript' => new Subscript(),
            'superscript' => new Superscript(),
            'small' => new Small(),
            'table' => new Table(),
            'table-cell' => new TableCell(),
            'table-header' => new TableHeader(),
            'table-row' => new TableRow(),
        ]);
    }
}
