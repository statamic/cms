<?php

namespace Statamic\Exceptions;

use Illuminate\Http\Request;
use Statamic\Statamic;

class ElevatedSessionAuthorizationException extends \Exception
{
    public function __construct()
    {
        parent::__construct(__('Requires an elevated session.'));
    }

    public function render(Request $request)
    {
        if ($request->wantsJson()) {
            return response()->json(['message' => $this->getMessage()], 403);
        }

        $redirectUrl = Statamic::isCpRoute()
            ? cp_route('confirm-password')
            : route('statamic.elevated-session');

        return redirect()->setIntendedUrl($request->fullUrl())->to($redirectUrl);
    }
}
