<?php

namespace Statamic\Exceptions;

use Illuminate\Http\Request;
use Statamic\Facades\URL;
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

        $intendedUrl = $request->isMethod('GET')
            ? $request->fullUrl()
            : $this->refererForIntendedUrl($request);

        return redirect()->setIntendedUrl($intendedUrl)->to($redirectUrl);
    }

    private function refererForIntendedUrl(Request $request)
    {
        $referer = $request->headers->get('referer');

        if ($referer && ! URL::isExternalToApplication($referer)) {
            return $referer;
        }

        return $request->fullUrl();
    }
}
