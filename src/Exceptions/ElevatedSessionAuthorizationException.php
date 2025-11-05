<?php

namespace Statamic\Exceptions;

use Illuminate\Http\Request;

class ElevatedSessionAuthorizationException extends \Exception
{
    public function __construct()
    {
        parent::__construct(__('Requires an elevated session.'));
    }

    public function render(Request $request)
    {
        return $request->wantsJson()
            ? response()->json(['message' => $this->getMessage()], 403)
            : redirect()->setIntendedUrl($request->fullUrl())->to('/cp/auth/confirm-password');
    }
}
