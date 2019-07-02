<?php

namespace Statamic\Policies;

class AssetFolderPolicy
{
    public function delete($user, $assetFolder)
    {
        // Ensure each asset within folder is deletable.
        return $assetFolder->assets()->reject(function ($asset) use ($user) {
            return $user->can('delete', $asset);
        })->isEmpty();
    }
}
