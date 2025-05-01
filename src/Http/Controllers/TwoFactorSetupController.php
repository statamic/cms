<?php

namespace Statamic\Http\Controllers;

use Illuminate\Http\Request;
use Statamic\Facades\User;

class TwoFactorSetupController extends Controller
{
    public function __construct(Request $request)
    {
        $this->middleware('auth');
    }

    public function __invoke(Request $request)
    {
        $user = User::fromUser($request->user());

        if ($user->hasEnabledTwoFactorAuthentication()) {
            return redirect($this->redirectPath());
        }

        return view('statamic::auth.two-factor.setup', [
            'routes' => $this->routes($user),
            'redirect' => $this->redirectPath(),
        ]);
    }

    protected function redirectPath()
    {
        return request('redirect') ?? route('statamic.site');
    }

    protected function routes($user): array
    {
        return [
            'enable' => route('statamic.users.two-factor.enable'),
            'recovery_codes' => [
                'show' => route('statamic.users.two-factor.recovery-codes.show'),
                'generate' => route('statamic.users.two-factor.recovery-codes.generate'),
                'download' => route('statamic.users.two-factor.recovery-codes.download'),
            ],
        ];
    }
}
