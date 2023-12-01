<?php

namespace Statamic\Fieldtypes;

use Statamic\Facades\Folder;
use Statamic\View\Views;

class TemplateFolder extends Relationship
{
    protected $component = 'template_folder';
    protected $selectable = false;

    protected function toItemArray($id, $site = null)
    {
        return ['title' => $id, 'id' => $id];
    }

    public function getIndexItems($request)
    {
        return Views::directories()
            ->map(fn ($folder) => ['id' => $folder, 'title' => $folder])
            ->values();
    }
}
