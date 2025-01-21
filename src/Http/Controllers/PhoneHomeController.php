<?php

namespace Statamic\Http\Controllers;

use Statamic\Exceptions\NotFoundHttpException;
use Statamic\Facades\Config;
use Statamic\Licensing\Outpost;

class PhoneHomeController
{
    public function __invoke(Outpost $outpost, $token)
    {
        if (! password_verify(Config::getLicenseKey(), base64_decode($token))) {
            throw new NotFoundHttpException;
        }

        $outpost->radio();

        return response()->json(['success' => true]);
    }
}
