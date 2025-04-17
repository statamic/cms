<?php

namespace Statamic\Auth\TwoFactor;

use BaconQrCode\Renderer\Color\Rgb;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\Fill;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Statamic\Exceptions\TwoFactorNotSetUpException;
use Statamic\Facades\User;

class Google2FA
{
    private $user;

    private \PragmaRX\Google2FA\Google2FA $provider;

    public function __construct()
    {
        $this->provider = app(\PragmaRX\Google2FA\Google2FA::class);
    }

    public function getQrCodeSvg()
    {
        $svg = (new Writer(
            new ImageRenderer(
                new RendererStyle(200, 0, null, null, Fill::uniformColor(new Rgb(255, 255, 255), new Rgb(45, 55, 72))),
                new SvgImageBackEnd
            )
        ))->writeString($this->getQrCode());

        return trim(substr($svg, strpos($svg, "\n") + 1));
    }

    public function getQrCode()
    {
        return $this->provider->getQRCodeUrl(
            config('app.name'),
            $this->user()->email(),
            $this->getSecretKey()
        );
    }

    public function getSecretKey()
    {
        $secret = $this->user()?->two_factor_secret;

        if (! $secret) {
            throw new TwoFactorNotSetUpException();
        }

        return decrypt($secret);
    }

    public function generateSecretKey()
    {
        return $this->provider->generateSecretKey();
    }

    public function verify($secret, $code)
    {
        $timestamp = $this->provider->verifyKey($secret, $code);

        if ($timestamp !== false) {
            return true;
        }

        return false;
    }

    public function setUser($user)
    {
        return $this->user = $user;
    }

    // todo: this is yucky. fix it.
    private function user()
    {
        return $this->user ?? User::current();
    }
}
