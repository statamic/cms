<?php

namespace Statamic\Data\Users\Redis;

use Statamic\API\Config;
use Statamic\Data\Users\User as FileUser;

class User extends FileUser
{
    /**
     * Get an array of data that should be persisted.
     *
     * @return array
     */
    public function toSavableArray()
    {
        return tap(parent::toSavableArray(), function (&$arr) {
            unset($arr['oauth_ids'], $arr['remember_token'], $arr['password_reset_token']);
            $arr['last_modified'] = time();
        });
    }

    /**
     * The timestamp of the last modification date.
     *
     * @return int
     */
    public function lastModified()
    {
        return $this->get('last_modified');
    }

    /**
     * Whether a file should be written to disk when saving.
     *
     * @return bool
     */
    protected function shouldWriteFile()
    {
        return Config::get('users.redis_write_file');
    }

    /**
     * Get the user's OAuth ID for the requested provider
     *
     * @return string
     */
    public function getOAuthId($provider)
    {
        return array_get($this->get('oauth_ids', []), $provider);
    }

    /**
     * Set a user's oauth ID
     *
     * @param string $provider
     * @param string $id
     * @return void
     */
    public function setOAuthId($provider, $id)
    {
        $ids = $this->get('oauth_ids', []);

        $ids[$provider] = $id;
        
        $this->set('oauth_ids', $ids);
    }

    /**
     * Get the token value for the "remember me" session.
     *
     * @return string
     */
    public function getRememberToken()
    {
        return $this->get('remember_token');
    }

    /**
     * Set the token value for the "remember me" session.
     *
     * @param  string  $value
     * @return void
     */
    public function setRememberToken($value)
    {
        $this->set('remember_token', $value);
    }

    /**
     * Set the reset token/code for a password reset
     *
     * @param  string $token
     * @return void
     */
    public function setPasswordResetToken($token)
    {
        $this->set('password_reset_token', $token);
    }

    /**
     * Get the reset token/code for a password reset
     *
     * @return string
     */
    public function getPasswordResetToken()
    {
        return $this->get('password_reset_token');
    }
}
