<?php

namespace Statamic\Http\Controllers;

use Statamic\Exceptions\NotFoundHttpException;
use Statamic\Facades\Config;
use Statamic\Licensing\Radio;

class PhoneHomeController
{
    public function __invoke(Radio $radio, $token)
    {
        if (! password_verify(Config::getLicenseKey(), base64_decode($token))) {
            throw new NotFoundHttpException;
        }

        $radio->forcePing();

        return response()->json(['success' => true]);
    }
}
