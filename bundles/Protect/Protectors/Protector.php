<?php

namespace Statamic\Addons\Protect\Protectors;

interface Protector
{
    /**
     * Whether or not this provides protection.
     *
     * @return bool
     */
    public function providesProtection();

    /**
     * Provide protection
     *
     * @return void
     */
    public function protect();

    /**
     * Deny access
     *
     * @return void
     */
    public function deny();
}