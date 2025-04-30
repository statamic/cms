<?php

namespace Statamic\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Statamic\Contracts\Auth\TwoFactor\TwoFactorAuthenticationProvider;

class TwoFactorChallengeRequest extends FormRequest
{
    /**
     * The user attempting the two factor challenge.
     *
     * @var mixed
     */
    protected $challengedUser;

    /**
     * Indicates if the user wished to be remembered after login.
     *
     * @var bool
     */
    protected $remember;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'code' => 'nullable|string',
            'recovery_code' => 'nullable|string',
        ];
    }

    /**
     * Determine if the request has a valid two factor code.
     *
     * @return bool
     */
    public function hasValidCode()
    {
        return $this->code && tap(app(TwoFactorAuthenticationProvider::class)->verify(
            decrypt($this->challengedUser()->two_factor_secret), $this->code
        ), function ($result) {
            if ($result) {
                $this->session()->forget('login.id');
            }
        });
    }

    /**
     * Get the valid recovery code if one exists on the request.
     *
     * @return string|null
     */
    public function validRecoveryCode()
    {
        if (! $this->recovery_code) {
            return;
        }

        return tap(collect($this->challengedUser()->recoveryCodes())->first(function ($code) {
            return hash_equals($code, $this->recovery_code) ? $code : null;
        }), function ($code) {
            if ($code) {
                $this->session()->forget('login.id');
            }
        });
    }

    /**
     * Determine if there is a challenged user in the current session.
     *
     * @return bool
     */
    public function hasChallengedUser()
    {
        if ($this->challengedUser) {
            return true;
        }

        return $this->session()->has('login.id') &&
            Auth::guard()->getProvider()->retrieveById($this->session()->get('login.id'));
    }

    /**
     * Get the user that is attempting the two factor challenge.
     *
     * @return mixed
     */
    public function challengedUser()
    {
        if ($this->challengedUser) {
            return $this->challengedUser;
        }

        if (
            ! $this->session()->has('login.id')
            || ! $user = Auth::guard()->getProvider()->retrieveById($this->session()->get('login.id'))
        ) {
            throw new HttpResponseException(
                $this->sendFailedTwoFactorChallengeResponse()
            );
        }

        return $this->challengedUser = $user;
    }

    /**
     * Determine if the user wanted to be remembered after login.
     *
     * @return bool
     */
    public function remember()
    {
        if (! $this->remember) {
            $this->remember = $this->session()->pull('login.remember', false);
        }

        return $this->remember;
    }

    public function sendFailedTwoFactorChallengeResponse()
    {
        [$key, $message] = $this->filled('recovery_code')
            ? ['recovery_code', __('statamic::validation.invalid_two_factor_recovery_code')]
            : ['code', __('statamic::validation.invalid_two_factor_code')];

        if ($this->wantsJson()) {
            throw ValidationException::withMessages([
                $key => [$message],
            ]);
        }

        return redirect()->route('statamic.cp.two-factor-challenge')->withErrors([$key => $message]);
    }
}
