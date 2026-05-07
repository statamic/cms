<?php

namespace Statamic\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Statamic\Facades\URL;
use Statamic\Facades\User;
use Statamic\Http\Middleware\CP\HandleInertiaRequests;

class TwoFactorSetupController extends Controller
{
    public function __construct(Request $request)
    {
        $this->middleware('auth');
        $this->middleware(HandleInertiaRequests::class);
    }

    public function __invoke(Request $request)
    {
        $user = User::fromUser($request->user());
        $redirect = $this->redirectPath();

        if ($user->hasEnabledTwoFactorAuthentication()) {
            return redirect($redirect);
        }

        return Inertia::render('auth/two-factor/Setup', [
            'routes' => $this->routes($user),
            'redirect' => $redirect,
        ]);
    }

    protected function redirectPath()
    {
        if (($redirect = request('redirect')) && ! URL::isExternalToApplication($redirect)) {
            return $redirect;
        }

        return redirect()->getIntendedUrl() ?? route('statamic.site');
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
