<?php

namespace Statamic\Auth;

use Statamic\Facades\URL;
use Statamic\Fields\Value;

trait HasAvatar
{
    /**
     * Get a user's avatar URL.
     *
     * Could be an asset thumbnail URL through a field named avatar, a Gravatar URL, or null.
     */
    public function avatar($size = 64)
    {
        if ($this->hasAvatarField() && ($url = $this->avatarFieldSquareThumbnailUrl())) {
            return $url;
        }

        return $this->gravatarUrl($size);
    }

    /**
     * Whether or not the user has an avatar field.
     */
    public function hasAvatarField()
    {
        return $this->get('avatar') && $this->blueprint()->hasField('avatar');
    }

    /**
     * The Value object holding the avatar asset.
     */
    public function avatarFieldValue()
    {
        return new Value(
            $this->get('avatar'),
            'avatar',
            $this->blueprint()->field('avatar')->fieldtype(),
            $this
        );
    }

    /**
     * The URL of the avatar from the asset field.
     */
    public function avatarFieldUrl()
    {
        return optional($this->avatarFieldValue()->value())->url();
    }

    /**
     * Square thumbnail URL of the avatar from the asset field.
     */
    public function avatarFieldSquareThumbnailUrl()
    {
        $assetId = optional($this->avatarFieldValue()->value())->id();

        if (! $assetId) {
            return null;
        }

        return cp_route('assets.thumbnails.show', [
            'encoded_asset' => base64_encode($assetId),
            'size' => 'small',
            'orientation' => 'square',
        ]);
    }

    /**
     * The Gravatar URL.
     */
    public function gravatarUrl($size = 64)
    {
        return config('statamic.users.avatars') === 'gravatar'
            ? URL::gravatar($this->email(), $size)
            : null;
    }
}
