<?php

namespace Statamic\Addons\User;

use Statamic\API\Str;
use Statamic\API\URL;
use Statamic\API\User;
use Statamic\API\Email;
use Statamic\API\Config;

class PasswordReset
{
    /**
     * @var string
     */
    private $code;

    /**
     * @var string
     */
    private $base_url;

    /**
     * @var Statamic\Contracts\Data\Users\User
     */
    private $user;

    /**
     * Get or set the user
     *
     * @param  Statamic\Contracts\Data\Users\User|null $user
     * @return Statamic\Contracts\Data\Users\User
     */
    public function user($user = null)
    {
        if (is_null($user)) {
            return $this->user;
        }

        $this->user = $user;
    }

    /**
     * Get or set the base reset form URL
     *
     * @param  string|null $url  The url of the form
     * @return string
     */
    public function baseUrl($url = null)
    {
        if (is_null($url)) {
            return $this->base_url;
        }

        $this->base_url = $url;
    }

    /**
     * Get the full reset form URL
     *
     * @return string
     */
    public function url()
    {
        $code = hash_hmac('sha256', Str::random(40), Config::getAppKey());

        $this->user->setPasswordResetToken($code);

        $this->user->save();

        $url = $this->base_url ?: EVENT_ROUTE.'/user/reset';
        $url .= "?user={$this->user->id()}&code={$code}";

        return URL::makeAbsolute(URL::prependSiteUrl($url));
    }

    /**
     * Get or set the code
     *
     * @param  string|null $code The reset code
     * @return string
     */
    public function code($code = null)
    {
        if (is_null($code)) {
            return $this->code;
        }

        $this->code = $code;
    }

    /**
     * Is the code valid?
     *
     * @return bool
     */
    public function valid()
    {
        return $this->user->getPasswordResetToken() == $this->code;
    }

    /**
     * Updates a user's password
     *
     * @param  string $password
     */
    public function updatePassword($password)
    {
        $this->user->password($password);

        $this->user->setPasswordResetToken(null);

        $this->user->save();
    }

    /**
     * Send a password reset email
     */
    public function send()
    {
        $subject = ($this->user->status() === 'pending')
            ? translate('passwords.activate_subject')
            : translate('passwords.reset_subject');

        $template = ($this->user->status() === 'pending') ? 'user-activation' : 'user-reset';

        Email::to($this->user->email())
             ->subject($subject)
             ->template($template)
             ->with(['reset_url' => $this->url()])
             ->send();
    }
}
