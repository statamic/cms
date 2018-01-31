<?php

namespace Statamic\Auth\Protect;

use Statamic\Auth\Protect\Protectors\Protector;
use Statamic\Auth\Protect\Protectors\IpProtector;
use Statamic\Auth\Protect\Protectors\NullProtector;
use Statamic\Auth\Protect\Protectors\PasswordProtector;
use Statamic\Auth\Protect\Protectors\LoggedInProtector;

class ProtectorManager
{
    /**
     * @var string
     */
    private $url;

    /**
     * @var array
     */
    protected $scheme;

    /**
     * @var bool
     */
    private $siteWide;

    /**
     * Protection classes in order of priority
     *
     * @var array
     */
    protected $protectors = [
        'password' => PasswordProtector::class,
        'ip_address' => IpProtector::class,
        'logged_in' => LoggedInProtector::class,
    ];

    /**
     * @param string $url
     * @param array  $scheme
     */
    public function __construct($url, array $scheme, $siteWide)
    {
        $this->url = $url;
        $this->scheme = $scheme;
        $this->siteWide = $siteWide;
    }

    /**
     * Provide protection
     *
     * @return void
     */
    public function protect($type)
    {
        $this->getProtectionProvider($type)->protect();
    }

    /**
     * Get the class that will provide protection
     *
     * @return Protector
     */
    protected function getProtectionProvider($type)
    {
        $protector = $this->resolveProtector(
            array_get($this->protectors, $type, NullProtector::class)
        );

        if (! $protector->providesProtection()) {
            return $this->resolveProtector(NullProtector::class);
        }

        return $protector;
    }

    /**
     * Create an instance of a protector
     *
     * @param string $class
     * @return Protector
     */
    protected function resolveProtector($class)
    {
        return new $class($this->url, $this->scheme, $this->siteWide);
    }
}
