<?php

namespace Statamic\Addons\Protect\Protectors;

abstract class AbstractProtector implements Protector
{
    /**
     * @var array
     */
    protected $scheme;

    /**
     * @var string
     */
    protected $url;

    /**
     * @var bool
     */
    protected $siteWide;

    /**
     * @param string $url
     * @param array  $scheme
     */
    public function __construct($url, array $scheme, $siteWide = false)
    {
        $this->scheme = $scheme;
        $this->url = $url;
        $this->siteWide = $siteWide;
    }

    /**
     * Provide protection
     *
     * @return void
     */
    abstract public function protect();

    /**
     * Deny access
     *
     * @return void
     */
    public function deny()
    {
        abort(403);
    }
}
