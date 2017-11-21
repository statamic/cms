<?php

namespace Statamic\Data\Pages;

use Statamic\API\File;
use Statamic\API\YAML;
use Statamic\Data\DataFolder;
use Statamic\Contracts\Data\Pages\PageFolder as PageFolderContract;

class PageFolder extends DataFolder implements PageFolderContract
{
    /**
     * Get the last modified date
     *
     * @return \Carbon\Carbon
     */
    public function lastModified()
    {
        // TODO: Implement lastModified() method.
    }

    /**
     * Save the folder
     *
     * @return mixed
     */
    public function save()
    {
        $path = 'pages/' . $this->path() . '/folder.yaml';

        File::disk('content')->put($path, YAML::dump($this->data()));
    }

    /**
     * Delete the folder
     *
     * @return mixed
     */
    public function delete()
    {
        // TODO: Implement delete() method.
    }

    public function editUrl()
    {
         // TODO: Implement editUrl() method.
    }
}
