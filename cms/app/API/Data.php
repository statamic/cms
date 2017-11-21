<?php

namespace Statamic\API;

class Data
{
    /**
     * Get data by ID
     *
     * @param string $id
     * @return mixed
     */
    public static function find($id)
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