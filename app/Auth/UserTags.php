<?php

namespace Statamic\Auth;

use Statamic\Support\Arr;
use Statamic\Facades\URL;
use Statamic\Facades\User;
use Statamic\Tags\Tags;
use Statamic\Exceptions\RedirectException;
use Statamic\Contracts\Auth\User as UserContract;

class UserTags extends Tags
{
    protected static $handle = 'user';

    /**
     * Dynamically fetch a user's data by variable_name
     *
     * Maps to {{ user:variable_name }}
     *
     * @return string
     */
    public function __call($method, $args)
    {
        $id = Arr::get($this->context, $method);

        if (! $user = User::find($id)) {
            return;
        }

        return $this->parseUser($user);
    }

    /**
     * Fetch a user
     *
     * Maps to {{ user }}
     *
     * @return string
     */
    public function index()
    {
        $user = null;

        // Get a user by ID, if the `id` parameter was used.
        if ($id = $this->get('id')) {
            if (! $user = User::find($id)) {
                return $this->parseNoResults();
            }
        }

        // Get a user by username, if the `username` parameter was used.
        if ($username = $this->get('username')) {
            if (! $user = User::whereUsername($username)) {
                return $this->parseNoResults();
            }
        }

        // Get a user by email, if the `email` parameter was used.
        if ($email = $this->get('email')) {
            if (! $user = User::whereEmail($email)) {
                return $this->parseNoResults();
            }
        }

        // No user found? Get the current one.
        if (! $user) {
            if (! $user = User::current()) {
                return $this->parseNoResults();
            }
        }

        return $this->parseUser($user);
    }

    /**
     * Alias of the {{ user }} tag.
     *
     * @return string
     */
    public function profile()
    {
        return $this->index();
    }

    /**
     * Output a login form.
     *
     * Maps to {{ user:login_form }}
     *
     * @return string
     */
    public function loginForm()
    {
        $data = [];

        if (session('errors')) {
            $data = ['errors' => session('errors')->all()];
        }

        $html = $this->formOpen('login');

        if ($redirect = $this->getRedirectUrl()) {
            $html .= '<input type="hidden" name="redirect" value="'.$redirect.'" />';
        }

        $html .= $this->parse($data);

        $html .= '</form>';

        return $html;
    }

    /**
     * Output a registration form
     *
     * Maps to {{ user:register_form }}
     *
     * @return string
     */
    public function registerForm()
    {
        $data = [];

        if (session('errors')) {
            $data = ['errors' => session('errors')->all()];
        }

        $html = $this->formOpen('register');

        if ($redirect = $this->getRedirectUrl()) {
            $html .= '<input type="hidden" name="redirect" value="'.$redirect.'" />';
        }

        $html .= $this->parse($data);

        $html .= '</form>';

        return $html;
    }

    /**
     * Alias of {{ user:register_form }}
     *
     * @return string
     */
    public function registrationForm()
    {
        return $this->registerForm();
    }

    /**
     * Outputs a logout URL
     *
     * Maps to {{ user:logout_url }}
     *
     * @return string
     */
    public function logoutUrl()
    {
        $url = 'logout';

        if ($redirect = $this->get('redirect')) {
            $url .= '?redirect='.$redirect;
        }

        return route('statamic.logout');
    }

    /**
     * Logs a user out and performs a redirect
     *
     * Maps to {{ user:logout }}
     *
     * @throws RedirectException
     */
    public function logout()
    {
        \Auth::logout();

        $e = new RedirectException;

        $e->setUrl($this->get('redirect', '/'));
        $e->setCode($this->get('response', 302));

        throw $e;
    }

    /**
     * Output a forgot password form
     *
     * Maps to {{ user:forgot_password_form }}
     *
     * @return string
     */
    public function forgotPasswordForm()
    {
        $data = [
            'errors' => [],
        ];

        if (session('email_sent')) {
            return $this->parse(['email_sent' => true, 'success' => true]);
        }

        if (session('errors')) {
            $data['errors'] = session('errors')->all();
        }

        $html = $this->formOpen(route('statamic.password.email'));

        if ($redirect = $this->getRedirectUrl()) {
            $html .= '<input type="hidden" name="redirect" value="'.$redirect.'" />';
        }

        if ($reset_url = $this->get('reset_url')) {
            $html .= '<input type="hidden" name="reset_url" value="'.$reset_url.'" />';
        }

        $html .= $this->parse($data);

        $html .= '</form>';

        return $html;
    }

    /**
     * Output a reset password form
     *
     * Maps to {{ user:reset_password_form }}
     *
     * @return string
     */
    public function resetPasswordForm()
    {
        $data = [
            'errors' => [],
        ];

        if (session('success')) {
            return $this->parse(['success' => true]);
        }

        if (session('errors')) {
            $data['errors'] = session('errors')->all();
        }

        $html = $this->formOpen(route('statamic.password.reset.action'));

        $html .= '<input type="hidden" name="token" value="'.request('token').'" />';

        if ($redirect = $this->get('redirect')) {
            $html .= '<input type="hidden" name="redirect" value="'.$redirect.'" />';
        }

        $html .= $this->parse($data);

        $html .= '</form>';

        return $html;
    }

    /**
     * Displays content if a user has permission
     *
     * Maps to {{ user:can }}
     *
     * @return string
     */
    public function can()
    {
        $permission = $this->get(['permission', 'do']);

        if ($this->api('Can')->can($permission)) {
            return $this->parse([]);
        }
    }

    /**
     * Displays content if a user doesn't have permission
     *
     * Maps to {{ user:cant }}
     *
     * @return string
     */
    public function cant()
    {
        $permission = $this->get(['permission', 'do']);

        if (! $this->api('Can')->can($permission)) {
            return $this->parse([]);
        }
    }

    /**
     * Displays content if a user is a role
     *
     * Maps to {{ user:is }}
     *
     * @return string
     */
    public function is()
    {
        $role = $this->get(['role', 'roles']);

        if ($this->api('Is')->is($role)) {
            return $this->parse([]);
        }
    }

    /**
     * Displays content if a user is not a role
     *
     * Maps to {{ user:isnt }}
     *
     * @return string
     */
    public function isnt()
    {
        $role = $this->get(['role', 'roles']);

        if (! $this->api('Is')->is($role)) {
            return $this->parse([]);
        }
    }

    /**
     * Displays content if a user is in a group
     *
     * Maps to {{ user:in }}
     *
     * @return string
     */
    public function in()
    {
        $group = $this->get(['group', 'groups']);

        if ($this->api('In')->in($group)) {
            return $this->parse([]);
        }
    }

    /**
     * Displays content if a user isn't in a group
     *
     * Maps to {{ user:not_in }}
     *
     * @return string
     */
    public function notIn()
    {
        $group = $this->get(['group', 'groups']);

        if (! $this->api('In')->in($group)) {
            return $this->parse([]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function eventUrl($url, $relative = false)
    {
        return URL::prependSiteUrl(
            config('statamic.routes.action') . '/user/' . $url
        );
    }

    /**
     * Get the redirect URL
     *
     * @return string
     */
    private function getRedirectUrl()
    {
        $return = $this->get('redirect');

        if ($this->getBool('allow_request_redirect')) {
            $return = request()->input('redirect', $return);
        }

        return $return;
    }

    private function parseUser(UserContract $user)
    {
        return $this->parse($user->toArray());
    }
}
