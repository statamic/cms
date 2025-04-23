<?php

namespace Statamic\Http\Controllers;

use Illuminate\Http\Request;

class TwoFactorSetupController extends Controller
{
    public function __construct(Request $request)
    {
        $this->middleware('auth');
    }

    public function __invoke(Request $request)
    {
        $user = $request->user();

        if ($user->hasEnabledTwoFactorAuthentication()) {
            return redirect($this->redirectPath());
        }

        return view('statamic::auth.two-factor.setup', [
            'routes' => [
                'enable' => cp_route('users.two-factor.enable', $user->id),
                'recovery_codes' => [
                    'show' => cp_route('users.two-factor.recovery-codes.show', $user->id),
                    'generate' => cp_route('users.two-factor.recovery-codes.generate', $user->id),
                    'download' => cp_route('users.two-factor.recovery-codes.download', $user->id),
                ],
            ],
            'redirect' => $this->redirectPath(),
        ]);
    }

    protected function redirectPath()
    {
        return request('redirect') ?? route('statamic.site');
    }
}
