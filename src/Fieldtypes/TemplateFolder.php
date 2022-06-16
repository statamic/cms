<?php

namespace Statamic\Fieldtypes;

use Statamic\Facades\Folder;

class TemplateFolder extends Relationship
{
    protected $component = 'template_folder';

    protected function toItemArray($id, $site = null)
    {
        return ['title' => $id, 'id' => $id];
    }

    public function getIndexItems($request)
    {
        return Folder::disk('resources')
            ->getFoldersRecursively('views')
            ->map(function ($folder) {
                $path = str_replace_first('views/', '', $folder);

                return [
                    'id' => $path,
                    'title' => $path,
                ];
            })
            ->prepend(['id' => '/', 'title' => '/'])
            ->values();
    }
}
