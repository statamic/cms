<?php

namespace Statamic\API\Endpoint;

use Statamic\API\User;
use Statamic\API\Asset;
use Statamic\API\Content;

class Data
{
    /**
     * Get data by ID
     *
     * @param string $id
     * @return mixed
     */
    public function find($id)
    {
        if ($content = Content::find($id)) {
            return $content;
        }

        if ($asset = Asset::find($id)) {
            return $asset;
        }

        if ($user = User::find($id)) {
            return $user;
        }
    }
}