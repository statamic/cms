<?php

namespace Statamic\Contracts\Data\Users;

interface User
{
    /**
     * Get or set a user's username
     *
     * @param string|null $username
     * @return mixed
     */
    public function username($username = null);

    /**
     * Get or set a user's email address
     *
     * @param string|null $email
     * @return mixed
     */
    public function email($email = null);

    /**
     * Get or set a user's password
     *
     * @param string|null $password
     * @return string
     */
    public function password($password = null);

    /**
     * Has this user's password been secured?
     *
     * @return mixed
     */
    public function isSecured();

    /**
     * Get the user's status
     *
     * @return string
     */
    public function status();

    /**
     * Set the reset token/code for a password reset
     *
     * @param  string $token
     * @return void
     */
    public function setPasswordResetToken($token);

    /**
     * Get the reset token/code for a password reset
     *
     * @return string
     */
    public function getPasswordResetToken();
}
