<?php

namespace Statamic\Auth;

use Statamic\Facades\Blueprint;
use Statamic\Facades\URL;
use Statamic\Facades\User;
use Statamic\Fields\Field;
use Statamic\Support\Arr;
use Statamic\Tags\Concerns;
use Statamic\Tags\Tags;

class UserTags extends Tags
{
    use Concerns\GetsFormSession,
        Concerns\GetsRedirects,
        Concerns\RendersForms;

    protected static $handle = 'user';

    /**
     * Dynamically fetch a user's data by variable_name.
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

        return $user;
    }

    /**
     * Fetch a user.
     *
     * Maps to {{ user }}
     *
     * @return string
     */
    public function index()
    {
        $user = null;

        // Get a user by ID, if the `id` parameter was used.
        if ($id = $this->params->get('id')) {
            if (! $user = User::find($id)) {
                return $this->parseNoResults();
            }
        }

        // Get a user by email, if the `email` parameter was used.
        if ($email = $this->params->get('email')) {
            if (! $user = User::findByEmail($email)) {
                return $this->parseNoResults();
            }
        }

        // Get a user by field, if the `field` parameter was used.
        if ($field = $this->params->get('field')) {
            if (! $user = User::query()->where($field, $this->params->get('value'))->first()) {
                return $this->parseNoResults();
            }
        }

        // No user found? Get the current one.
        if (! $user) {
            if (! $user = User::current()) {
                return $this->parseNoResults();
            }
        }

        return $user;
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
        $data = $this->getFormSession();

        $knownParams = ['redirect', 'error_redirect', 'allow_request_redirect'];

        $html = $this->formOpen(route('statamic.login'), 'POST', $knownParams);

        $params = [];

        if ($redirect = $this->getRedirectUrl()) {
            $params['redirect'] = $this->parseRedirect($redirect);
        }

        if ($errorRedirect = $this->getErrorRedirectUrl()) {
            $params['error_redirect'] = $this->parseRedirect($errorRedirect);
        }

        $html .= $this->formMetaFields($params);

        $html .= $this->parse($data);

        $html .= $this->formClose();

        return $html;
    }

    /**
     * Output a registration form.
     *
     * Maps to {{ user:register_form }}
     *
     * @return string
     */
    public function registerForm()
    {
        $data = $this->getFormSession('user.register');

        $data['fields'] = $this->getRegistrationFields();

        $knownParams = ['redirect', 'error_redirect', 'allow_request_redirect'];

        $html = $this->formOpen(route('statamic.register'), 'POST', $knownParams);

        $params = [];

        if ($redirect = $this->getRedirectUrl()) {
            $params['redirect'] = $this->parseRedirect($redirect);
        }

        if ($errorRedirect = $this->getErrorRedirectUrl()) {
            $params['error_redirect'] = $this->parseRedirect($errorRedirect);
        }

        $html .= $this->formMetaFields($params);

        $html .= $this->parse($data);

        $html .= $this->formClose();

        return $html;
    }

    /**
     * Alias of {{ user:register_form }}.
     *
     * @return string
     */
    public function registrationForm()
    {
        return $this->registerForm();
    }

    /**
     * Outputs a logout URL.
     *
     * Maps to {{ user:logout_url }}
     *
     * @return string
     */
    public function logoutUrl()
    {
        $queryParams = [];

        if ($redirect = $this->params->get('redirect')) {
            $queryParams['redirect'] = $redirect;
        }

        return route('statamic.logout', $queryParams);
    }

    /**
     * Logs a user out and performs a redirect.
     *
     * Maps to {{ user:logout }}
     */
    public function logout()
    {
        auth()->logout();

        abort(redirect($this->params->get('redirect', '/'), $this->params->get('response', 302)));
    }

    /**
     * Output a forgot password form.
     *
     * Maps to {{ user:forgot_password_form }}
     *
     * @return string
     */
    public function forgotPasswordForm()
    {
        $data = $this->getFormSession('user.forgot_password');

        // Alias for backwards compatibility.
        $data['email_sent'] = $data['success'];

        $knownParams = ['redirect', 'error_redirect', 'allow_request_redirect', 'reset_url'];

        $html = $this->formOpen(route('statamic.password.email'), 'POST', $knownParams);

        $params = [];

        if ($redirect = $this->getRedirectUrl()) {
            $params['redirect'] = $this->parseRedirect($redirect);
        }

        if ($errorRedirect = $this->getErrorRedirectUrl()) {
            $params['error_redirect'] = $this->parseRedirect($errorRedirect);
        }

        if ($resetUrl = $this->params->get('reset_url')) {
            $params['reset_url'] = $resetUrl;
        }

        $html .= $this->formMetaFields($params);

        $html .= $this->parse($data);

        $html .= $this->formClose();

        return $html;
    }

    /**
     * Output a reset password form.
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

        $knownParams = ['redirect'];

        $html = $this->formOpen(route('statamic.password.reset.action'), 'POST', $knownParams);

        $html .= '<input type="hidden" name="token" value="'.request('token').'" />';

        if ($redirect = $this->params->get('redirect')) {
            $html .= '<input type="hidden" name="redirect" value="'.$redirect.'" />';
        }

        $html .= $this->parse($data);

        $html .= $this->formClose();

        return $html;
    }

    /**
     * Displays content if a user has permission.
     *
     * Maps to {{ user:can }}
     *
     * @return string
     */
    public function can()
    {
        if (! $user = User::current()) {
            return;
        }

        $permissions = Arr::wrap($this->params->explode(['permission', 'do']));

        foreach ($permissions as $permission) {
            if ($user->can($permission)) {
                return $this->parse();
            }
        }
    }

    /**
     * Displays content if a user doesn't have permission.
     *
     * Maps to {{ user:cant }}
     *
     * @return string
     */
    public function cant()
    {
        if (! $user = User::current()) {
            return $this->parse();
        }

        $permissions = Arr::wrap($this->params->explode(['permission', 'do']));

        $can = false;

        foreach ($permissions as $permission) {
            if ($user->can($permission)) {
                $can = true;
                break;
            }
        }

        return $can ? null : $this->parse();
    }

    /**
     * Displays content if a user is a role.
     *
     * Maps to {{ user:is }}
     *
     * @return string
     */
    public function is()
    {
        if (! $user = User::current()) {
            return;
        }

        $roles = Arr::wrap($this->params->explode(['role', 'roles']));

        foreach ($roles as $role) {
            if ($user->hasRole($role)) {
                return $this->parse();
            }
        }
    }

    /**
     * Displays content if a user is not a role.
     *
     * Maps to {{ user:isnt }}
     *
     * @return string
     */
    public function isnt()
    {
        if (! $user = User::current()) {
            return $this->parse();
        }

        $roles = Arr::wrap($this->params->explode(['roles', 'role']));

        $is = false;

        foreach ($roles as $permission) {
            if ($user->hasRole($permission)) {
                $is = true;
                break;
            }
        }

        return $is ? null : $this->parse();
    }

    /**
     * Displays content if a user is in a group.
     *
     * Maps to {{ user:in }}
     *
     * @return string
     */
    public function in()
    {
        if (! $user = User::current()) {
            return;
        }

        $groups = Arr::wrap($this->params->explode(['group', 'groups']));

        foreach ($groups as $group) {
            if ($user->isInGroup($group)) {
                return $this->parse();
            }
        }
    }

    /**
     * Displays content if a user isn't in a group.
     *
     * Maps to {{ user:not_in }}
     *
     * @return string
     */
    public function notIn()
    {
        if (! $user = User::current()) {
            return $this->parse();
        }

        $groups = Arr::wrap($this->params->explode(['groups', 'group']));

        $in = false;

        foreach ($groups as $permission) {
            if ($user->isInGroup($permission)) {
                $in = true;
                break;
            }
        }

        return $in ? null : $this->parse();
    }

    /**
     * {@inheritdoc}
     */
    public function eventUrl($url, $relative = false)
    {
        return URL::prependSiteUrl(
            config('statamic.routes.action').'/user/'.$url
        );
    }

    /**
     * Get the redirect URL.
     *
     * @return string
     */
    protected function getRedirectUrl()
    {
        $return = $this->params->get('redirect');

        if ($this->params->bool('allow_request_redirect', false)) {
            $return = request()->input('redirect', $return);
        }

        return $return;
    }

    /**
     * Get fields with extra data for looping over and rendering.
     *
     * @return array
     */
    protected function getRegistrationFields()
    {
        return array_merge(
            $this->getRequiredRegistrationFields(),
            $this->getAdditionalRegistrationFields()
        );
    }

    /**
     * Get additional registration fields from the user blueprint.
     *
     * @return array
     */
    protected function getRequiredRegistrationFields()
    {
        $blueprintFields = User::blueprint()->fields()->all()
            ->keyBy->handle()
            ->filter(function ($field, $handle) {
                return in_array($handle, ['email', 'password']);
            });

        return collect()
            ->put('email', new Field('email', [
                'type' => 'text',
                'input_type' => 'email',
                'display' => __('Email Address'),
            ]))
            ->put('password', new Field('password', [
                'type' => 'text',
                'input_type' => 'password',
                'display' => __('Password'),
            ]))
            ->put('password_confirmation', new Field('password_confirmation', [
                'type' => 'text',
                'input_type' => 'password',
                'display' => __('Password Confirmation'),
            ]))
            ->merge($blueprintFields)
            ->map(function ($field) {
                return $this->getRenderableField($field, 'user.register');
            })
            ->values()
            ->all();
    }

    /**
     * Get additional registration fields from the user blueprint.
     *
     * @return array
     */
    protected function getAdditionalRegistrationFields()
    {
        return User::blueprint()->fields()->all()
            ->reject(function ($field) {
                return in_array($field->handle(), ['email', 'password', 'password_confirmation', 'roles', 'groups']);
            })
            ->map(function ($field) {
                return $this->getRenderableField($field, 'user.register');
            })
            ->values()
            ->all();
    }
}
