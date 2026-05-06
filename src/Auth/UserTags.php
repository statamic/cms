<?php

namespace Statamic\Auth;

use Illuminate\Support\Collection;
use Statamic\Contracts\Auth\Role;
use Statamic\Facades\TwoFactor;
use Statamic\Facades\URL;
use Statamic\Facades\User;
use Statamic\Fields\Field;
use Statamic\Support\Arr;
use Statamic\Support\Html;
use Statamic\Support\Str;
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
     * @return void|\Statamic\Contracts\Auth\User
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

        return $this->aliasedResult($user);
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
        $data = array_merge($this->getFormSession(), [
            'passkey_options_url' => route('statamic.passkeys.options'),
            'passkey_verify_url' => route('statamic.passkeys.login'),
        ]);

        $knownParams = ['redirect', 'error_redirect', 'allow_request_redirect'];

        $action = route('statamic.login');
        $method = 'POST';

        $params = [];

        if ($redirect = $this->getRedirectUrl()) {
            $params['redirect'] = $this->parseRedirect($redirect);
        }

        if ($errorRedirect = $this->getErrorRedirectUrl()) {
            $params['error_redirect'] = $this->parseRedirect($errorRedirect);
        }

        if (! $this->canParseContents()) {
            return array_merge([
                'attrs' => $this->formAttrs($action, $method, $knownParams),
                'params' => $this->formMetaPrefix($this->formParams($method, $params)),
            ], $data);
        }

        $html = $this->formOpen($action, $method, $knownParams);

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

        $action = route('statamic.register');
        $method = 'POST';

        $params = [];

        if ($redirect = $this->getRedirectUrl()) {
            $params['redirect'] = $this->parseRedirect($redirect);
        }

        if ($errorRedirect = $this->getErrorRedirectUrl()) {
            $params['error_redirect'] = $this->parseRedirect($errorRedirect);
        }

        if (! $this->canParseContents()) {
            return array_merge([
                'attrs' => $this->formAttrs($action, $method, $knownParams),
                'params' => $this->formMetaPrefix($this->formParams($method, $params)),
            ], $data);
        }

        $html = $this->formOpen($action, $method, $knownParams);

        $html .= $this->formMetaFields($params);

        $html .= $this->parse($data);

        $html .= $this->formClose();

        return $html;
    }

    /**
     * Output a profile form.
     *
     * Maps to {{ user:profile_form }}
     *
     * @return string
     */
    public function profileForm()
    {
        if (session()->has('status')) {
            return $this->parse(['success' => true]);
        }

        $data = $this->getFormSession('user.profile');

        $data['tabs'] = $this->getProfileTabs();
        $data['sections'] = collect($data['tabs'])->flatMap->sections->all();
        $data['fields'] = collect($data['sections'])->flatMap->fields->all();

        $knownParams = ['redirect', 'error_redirect', 'allow_request_redirect'];

        $params = [];

        if ($redirect = $this->getRedirectUrl()) {
            $params['redirect'] = $this->parseRedirect($redirect);
        }

        if ($errorRedirect = $this->getErrorRedirectUrl()) {
            $params['error_redirect'] = $this->parseRedirect($errorRedirect);
        }

        $action = route('statamic.profile');
        $method = 'POST';

        if (! $this->canParseContents()) {
            return array_merge([
                'attrs' => $this->formAttrs($action, $method, $knownParams),
                'params' => $this->formMetaPrefix($this->formParams($method, $params)),
            ], $data);
        }

        $html = $this->formOpen($action, $method, $knownParams);

        $html .= $this->formMetaFields($params);

        $html .= $this->parse($data);

        $html .= $this->formClose();

        return $html;
    }

    /**
     * Output a password change form.
     *
     * Maps to {{ user:password_form }}
     *
     * @return string
     */
    public function passwordForm()
    {
        if (session()->has('status')) {
            return $this->parse(['success' => true]);
        }

        $data = $this->getFormSession('user.password');

        $data['fields'] = $this->getPasswordFields();

        $knownParams = ['redirect', 'error_redirect', 'allow_request_redirect'];

        $action = route('statamic.password');
        $method = 'POST';

        $params = [];

        if ($redirect = $this->getRedirectUrl()) {
            $params['redirect'] = $this->parseRedirect($redirect);
        }

        if ($errorRedirect = $this->getErrorRedirectUrl()) {
            $params['error_redirect'] = $this->parseRedirect($errorRedirect);
        }

        if (! $this->canParseContents()) {
            return array_merge([
                'attrs' => $this->formAttrs($action, $method, $knownParams),
                'params' => $this->formMetaPrefix($this->formParams($method, $params)),
            ], $data);
        }

        $html = $this->formOpen($action, $method, $knownParams);

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
     * Output a passkey registration form.
     *
     * Maps to {{ user:passkey_form }}
     *
     * @return string
     */
    public function passkeyForm()
    {
        $data = [
            'passkey_options_url' => route('statamic.passkeys.create'),
            'passkey_verify_url' => route('statamic.passkeys.store'),
        ];

        if (! $this->canParseContents()) {
            return $data;
        }

        return $this->parse($data);
    }

    /**
     * Output the current user's passkeys.
     *
     * Maps to {{ user:passkeys }}
     *
     * @return string
     */
    public function passkeys()
    {
        if (! $user = User::current()) {
            return $this->canParseContents() ? $this->parseNoResults() : [];
        }

        $passkeys = $user->passkeys()->map(function ($passkey) {
            return [
                'id' => $passkey->id(),
                'name' => $passkey->name(),
                'last_login' => $passkey->lastLogin(),
            ];
        })->values()->all();

        if (! $this->canParseContents()) {
            return $passkeys;
        }

        if (empty($passkeys)) {
            return $this->parseNoResults();
        }

        return $this->parseLoop($passkeys);
    }

    /**
     * Output a delete passkey form.
     *
     * Maps to {{ user:delete_passkey_form }}
     *
     * @return string
     */
    public function deletePasskeyForm()
    {
        if (! $user = User::current()) {
            return '';
        }

        $id = $this->params->get('id');

        if (! $id || ! $user->passkeys()->get($id)) {
            return '';
        }

        $action = route('statamic.passkeys.destroy', ['id' => $id]);
        $method = 'POST';

        $knownParams = ['id', 'redirect'];

        $params = [];

        if ($redirect = $this->getRedirectUrl()) {
            $params['redirect'] = $this->parseRedirect($redirect);
        }

        if (! $this->canParseContents()) {
            return [
                'attrs' => $this->formAttrs($action, $method, $knownParams),
                'params' => array_merge(
                    $this->formMetaPrefix($this->formParams($method, $params)),
                    ['_method' => 'DELETE']
                ),
            ];
        }

        $html = $this->formOpen($action, $method, $knownParams);

        $html .= '<input type="hidden" name="_method" value="DELETE" />';

        $html .= $this->formMetaFields($params);

        $html .= $this->parse([]);

        $html .= $this->formClose();

        return $html;
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

        $action = route('statamic.password.email');
        $method = 'POST';

        $params = [];

        if ($redirect = $this->getRedirectUrl()) {
            $params['redirect'] = $this->parseRedirect($redirect);
        }

        if ($errorRedirect = $this->getErrorRedirectUrl()) {
            $params['error_redirect'] = $this->parseRedirect($errorRedirect);
        }

        if ($resetUrl = $this->getPasswordResetUrl($this->params->get('reset_url'))) {
            $params['reset_url'] = $resetUrl;
        }

        if (! $this->canParseContents()) {
            return array_merge([
                'attrs' => $this->formAttrs($action, $method, $knownParams),
                'params' => $this->formMetaPrefix($this->formParams($method, $params)),
            ], $data);
        }

        $html = $this->formOpen($action, $method, $knownParams);

        $html .= $this->formMetaFields($params);

        $html .= $this->parse($data);

        $html .= $this->formClose();

        return $html;
    }

    private function getPasswordResetUrl(?string $url = null): ?string
    {
        if (! $url) {
            return null;
        }

        if (! URL::isAbsolute($url) && ! str_starts_with($url, '/')) {
            $url = '/'.$url;
        }

        return encrypt($url);
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
        if (session()->has('status')) {
            return $this->parse(['success' => true]);
        }

        $data = $this->getFormSession();

        $data['url_invalid'] = request()->isNotFilled('token');

        if (! $this->params->has('redirect')) {
            $this->params->put('redirect', request()->getPathInfo());
        }

        $action = route('statamic.password.reset.action');
        $method = 'POST';

        $token = Html::entities(request('token'));

        $knownParams = ['redirect', 'error_redirect', 'allow_request_redirect'];

        $params = [];

        $redirect = $this->getRedirectUrl();

        if ($errorRedirect = $this->getErrorRedirectUrl()) {
            if (Str::startsWith($errorRedirect, '#')) {
                $errorRedirect = request()->url().'?token='.$token.$errorRedirect;
            }

            $params['error_redirect'] = $errorRedirect;
        }

        if (! $this->canParseContents()) {
            return array_merge([
                'attrs' => $this->formAttrs($action, $method, $knownParams),
                'params' => array_merge($this->formMetaPrefix($this->formParams($method, $params)), [
                    'token' => $token,
                    'redirect' => $redirect,
                ]),
            ], $data);
        }

        $html = $this->formOpen($action, $method, $knownParams);

        $html .= $this->formMetaFields($params);

        $html .= '<input type="hidden" name="token" value="'.$token.'" />';

        if ($redirect) {
            $html .= '<input type="hidden" name="redirect" value="'.e($redirect).'" />';
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
     * @return string|void
     */
    public function can()
    {
        if (! $user = User::current()) {
            return $this->parser ? null : false;
        }

        $permissions = Arr::wrap($this->params->explode(['permission', 'do']));
        $arguments = $this->params->except(['permission', 'do'])->all();

        foreach ($permissions as $permission) {
            if ($user->can($permission, $arguments)) {
                return $this->parser ? $this->parse() : true;
            }
        }

        return $this->parser ? null : false;
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
            return $this->parser ? $this->parse() : true;
        }

        $permissions = Arr::wrap($this->params->explode(['permission', 'do']));
        $arguments = $this->params->except(['permission', 'do'])->all();

        $can = false;

        foreach ($permissions as $permission) {
            if ($user->can($permission, $arguments)) {
                $can = true;
                break;
            }
        }

        if (! $this->parser) {
            return ! $can;
        }

        return $can ? null : $this->parse();
    }

    /**
     * Displays content if a user is a role.
     *
     * Maps to {{ user:is }}
     *
     * @return string|void
     */
    public function is()
    {
        if (! $user = User::current()) {
            return $this->parser ? null : false;
        }

        $roles = $this->params->get(['role', 'roles']);

        if (! $roles instanceof Collection || ! $roles->every(fn ($role) => $role instanceof Role)) {
            $roles = Arr::wrap($this->params->explode(['role', 'roles']));
        }

        foreach ($roles as $role) {
            if ($user->hasRole($role)) {
                return $this->parser ? $this->parse() : true;
            }
        }

        return $this->parser ? null : false;
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
            return $this->parser ? $this->parse() : true;
        }

        $roles = $this->params->get(['role', 'roles']);

        if (! $roles instanceof Collection || ! $roles->every(fn ($role) => $role instanceof Role)) {
            $roles = Arr::wrap($this->params->explode(['roles', 'role']));
        }

        $is = false;

        foreach ($roles as $permission) {
            if ($user->hasRole($permission)) {
                $is = true;
                break;
            }
        }

        if (! $this->parser) {
            return ! $is;
        }

        return $is ? null : $this->parse();
    }

    /**
     * Displays content if a user is in a group.
     *
     * Maps to {{ user:in }}
     *
     * @return string|void
     */
    public function in()
    {
        if (! $user = User::current()) {
            return $this->parser ? null : false;
        }

        $groups = Arr::wrap($this->params->explode(['group', 'groups']));

        foreach ($groups as $group) {
            if ($user->isInGroup($group)) {
                return $this->parser ? $this->parse() : true;
            }
        }

        return $this->parser ? null : false;
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
            return $this->parser ? $this->parse() : true;
        }

        $groups = Arr::wrap($this->params->explode(['groups', 'group']));

        $in = false;

        foreach ($groups as $permission) {
            if ($user->isInGroup($permission)) {
                $in = true;
                break;
            }
        }

        if (! $this->parser) {
            return ! $in;
        }

        return $in ? null : $this->parse();
    }

    /**
     * Output an elevated session form.
     *
     * Maps to {{ user:elevated_session_form }}
     *
     * @return string
     */
    public function elevatedSessionForm()
    {
        if (! ($user = User::current())) {
            return;
        }

        $method = $user->getElevatedSessionMethod();

        if ($method === 'verification_code') {
            session()->sendElevatedSessionVerificationCodeIfRequired();
        }

        $data = [
            ...$this->getFormSession('user.elevated_session'),
            'method' => $method,
            'allow_passkey' => $method !== 'verification_code' && $user->passkeys()->isNotEmpty(),
            'resend_code_url' => route('statamic.elevated-session.resend-code'),
            'passkey_options_url' => route('statamic.elevated-session.passkey-options'),
            'submit_url' => route('statamic.elevated-session.confirm'),
        ];

        $action = route('statamic.elevated-session.confirm');
        $method = 'POST';

        if (! $this->canParseContents()) {
            return array_merge([
                'attrs' => $this->formAttrs($action, $method),
                'params' => $this->formMetaPrefix($this->formParams($method)),
            ], $data);
        }

        $html = $this->formOpen($action, $method);

        $html .= $this->parse($data);

        $html .= $this->formClose();

        return $html;
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
     * Output a boolean of whether two-factor auth is enabled for the user.
     *
     * Maps to {{ user:two_factor_enabled }}
     */
    public function twoFactorEnabled(): bool
    {
        return (bool) User::current()?->hasEnabledTwoFactorAuthentication();
    }

    /**
     * Output a two-factor challenge form for login verification.
     *
     * Maps to {{ user:two_factor_challenge_form }}
     *
     * @return string|array
     */
    public function twoFactorChallengeForm()
    {
        if (
            ! TwoFactor::enabled()
            || session()->missing('login.id')
        ) {
            return;
        }

        $params = [];

        $data = $this->getFormSession();

        $knownParams = ['redirect', 'error_redirect', 'allow_request_redirect'];

        $method = 'POST';
        $action = route('statamic.two-factor-challenge');

        if ($redirect = $this->getRedirectUrl()) {
            $params['redirect'] = $this->parseRedirect($redirect);
        }

        if ($errorRedirect = $this->getErrorRedirectUrl()) {
            $params['error_redirect'] = $this->parseRedirect($errorRedirect);
        }

        if (! $this->canParseContents()) {
            return array_merge([
                'attrs' => $this->formAttrs($action, $method, $knownParams),
                'params' => $this->formMetaPrefix($this->formParams($method, $params)),
            ], $data);
        }

        $html = $this->formOpen($action, $method, $knownParams);

        $html .= $this->formMetaFields($params);

        $html .= $this->parse($data);

        $html .= $this->formClose();

        return $html;
    }

    /**
     * Output a two-factor enable form.
     *
     * Maps to {{ user:two_factor_enable_form }}
     *
     * @return string|array
     */
    public function twoFactorEnableForm()
    {
        $user = User::current();

        if (
            ! TwoFactor::enabled()
            || ! $user
            || $user->hasEnabledTwoFactorAuthentication()
        ) {
            return;
        }

        $params = [];

        $data = $this->getFormSession('user.two_factor_enable');

        $knownParams = ['redirect', 'allow_request_redirect'];

        $method = 'POST';
        $action = route('statamic.users.two-factor.enable');

        if ($redirect = $this->getRedirectUrl()) {
            $params['redirect'] = $this->parseRedirect($redirect);
        }

        if (! $this->canParseContents()) {
            return array_merge([
                'attrs' => $this->formAttrs($action, $method, $knownParams),
                'params' => $this->formMetaPrefix($this->formParams($method, $params)),
            ], $data);
        }

        $html = $this->formOpen($action, $method, $knownParams);

        $html .= $this->formMetaFields($params);

        $html .= $this->parse($data);

        $html .= $this->formClose();

        return $html;
    }

    /**
     * Output a two-factor setup form.
     *
     * Maps to {{ user:two_factor_setup_form }}
     *
     * @return string|array
     */
    public function twoFactorSetupForm()
    {
        $user = User::current();

        if (
            ! TwoFactor::enabled()
            || ! $user
            || $user->hasEnabledTwoFactorAuthentication()
            || empty($user->two_factor_secret)
        ) {
            return;
        }

        $params = [];

        $data = $this->getFormSession('user.two_factor_setup');

        $data['qr_code'] = $user->twoFactorQrCodeSvg();
        $data['qr_code_url'] = 'data:image/svg+xml;base64,'.base64_encode($user->twoFactorQrCodeSvg());
        $data['secret_key'] = $user->twoFactorSecretKey();

        $knownParams = ['redirect', 'error_redirect', 'allow_request_redirect'];

        $method = 'POST';
        $action = route('statamic.users.two-factor.confirm');

        if ($redirect = $this->getRedirectUrl()) {
            $params['redirect'] = $this->parseRedirect($redirect);
        }

        if ($errorRedirect = $this->getErrorRedirectUrl()) {
            $params['error_redirect'] = $this->parseRedirect($errorRedirect);
        }

        if (! $this->canParseContents()) {
            return array_merge([
                'attrs' => $this->formAttrs($action, $method, $knownParams),
                'params' => $this->formMetaPrefix($this->formParams($method, $params)),
            ], $data);
        }

        $html = $this->formOpen($action, $method, $knownParams);

        $html .= $this->formMetaFields($params);

        $html .= $this->parse($data);

        $html .= $this->formClose();

        return $html;
    }

    /**
     * Output the user's two-factor recovery codes.
     *
     * Maps to {{ user:two_factor_recovery_codes }}
     *
     * @return array|string
     */
    public function twoFactorRecoveryCodes()
    {
        $user = User::current();

        if (
            ! TwoFactor::enabled()
            || ! $user?->hasEnabledTwoFactorAuthentication()
        ) {
            return $this->parser ? null : [];
        }

        $codes = collect($user->twoFactorRecoveryCodes())->map(fn ($code) => ['code' => $code]);

        return $this->parser ? $this->parseLoop($codes) : $codes->all();
    }

    /**
     * Outputs a URL to download two-factor recovery codes.
     *
     * Maps to {{ user:two_factor_recovery_codes_download_url }}
     *
     * @return string
     */
    public function twoFactorRecoveryCodesDownloadUrl()
    {
        $user = User::current();

        if (
            ! TwoFactor::enabled()
            || ! $user?->hasEnabledTwoFactorAuthentication()
        ) {
            return;
        }

        return route('statamic.users.two-factor.recovery-codes.download');
    }

    /**
     * Output a form to regenerate two-factor recovery codes.
     *
     * Maps to {{ user:reset_two_factor_recovery_codes_form }}
     *
     * @return string|array
     */
    public function resetTwoFactorRecoveryCodesForm()
    {
        $user = User::current();

        if (
            ! TwoFactor::enabled()
            || ! $user?->hasEnabledTwoFactorAuthentication()
        ) {
            return;
        }

        $params = [];

        $data = $this->getFormSession('user.two_factor_reset_recovery_codes');

        $knownParams = ['redirect', 'allow_request_redirect'];

        $method = 'POST';
        $action = route('statamic.users.two-factor.recovery-codes.generate');

        if ($redirect = $this->getRedirectUrl()) {
            $params['redirect'] = $this->parseRedirect($redirect);
        }

        if (! $this->canParseContents()) {
            return array_merge([
                'attrs' => $this->formAttrs($action, $method, $knownParams),
                'params' => $this->formMetaPrefix($this->formParams($method, $params)),
            ], $data);
        }

        $html = $this->formOpen($action, $method, $knownParams);

        $html .= $this->formMetaFields($params);

        $html .= $this->parse($data);

        $html .= $this->formClose();

        return $html;
    }

    /**
     * Output a form to disable two-factor authentication.
     *
     * Maps to {{ user:disable_two_factor_form }}
     *
     * @return string|array
     */
    public function disableTwoFactorForm()
    {
        $user = User::current();

        if (
            ! TwoFactor::enabled()
            || ! $user?->hasEnabledTwoFactorAuthentication()
        ) {
            return;
        }

        $params = [];

        $data = $this->getFormSession('user.two_factor_disable');

        $knownParams = ['redirect', 'allow_request_redirect'];

        $method = 'DELETE';
        $action = route('statamic.users.two-factor.disable');

        if ($redirect = $this->getRedirectUrl()) {
            $params['redirect'] = $this->parseRedirect($redirect);
        }

        if (! $this->canParseContents()) {
            return array_merge([
                'attrs' => $this->formAttrs($action, $method, $knownParams),
                'params' => $this->formMetaPrefix($this->formParams($method, $params)),
            ], $data);
        }

        $html = $this->formOpen($action, $method, $knownParams);

        $html .= $this->formMetaFields($params);

        $html .= $this->parse($data);

        $html .= $this->formClose();

        return $html;
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

    /**
     * Get tabs, sections, and fields with extra data for looping over and rendering.
     *
     * @return array
     */
    protected function getProfileTabs()
    {
        $user = User::current();

        $values = $user
            ? $user->data()->merge(['email' => $user->email()])->all()
            : [];

        return User::blueprint()->tabs()
            ->map(fn ($tab) => [
                'display' => $tab->display(),
                'sections' => $tab->sections()
                    ->map(fn ($section) => [
                        'display' => $section->display(),
                        'instructions' => $section->instructions(),
                        'fields' => $section->fields()->addValues($values)->preProcess()->all()
                            ->reject(fn ($field) => in_array($field->handle(), ['password', 'password_confirmation', 'roles', 'groups'])
                                    || $field->fieldtype()->handle() === 'assets'
                            )
                            ->map(fn ($field) => $this->getRenderableField($field, 'user.profile'))
                            ->values()
                            ->all(),
                    ])
                    ->all(),
            ])
            ->values()
            ->all();
    }

    /**
     * Get fields with extra data for looping over and rendering.
     *
     * @return array
     *
     * @deprecated
     */
    protected function getProfileFields()
    {
        $user = User::current();

        $values = $user
            ? $user->data()->merge(['email' => $user->email()])->all()
            : [];

        return User::blueprint()->fields()->addValues($values)->preProcess()->all()
            ->reject(function ($field) {
                return in_array($field->handle(), ['password', 'password_confirmation', 'roles', 'groups'])
                    || $field->fieldtype()->handle() === 'assets';
            })
            ->map(function ($field) {
                return $this->getRenderableField($field, 'user.profile');
            })
            ->values()
            ->all();
    }

    /**
     * Get fields with extra data for looping over and rendering.
     *
     * @return array
     */
    protected function getPasswordFields()
    {
        return collect()
            ->put('current_password', new Field('current_password', [
                'type' => 'text',
                'input_type' => 'password',
                'display' => __('Current Password'),
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
            ->map(function ($field) {
                return $this->getRenderableField($field, 'user.password');
            })
            ->values()
            ->all();
    }
}
