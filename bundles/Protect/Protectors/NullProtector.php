<?php

namespace Statamic\Addons\Protect\Protectors;

class NullProtector extends AbstractProtector
{
    /**
     * Whether or not this provides protection.
     *
     * @return bool
     */
    public function providesProtection()
    {
        return true;
    }

    /**
     * Provide protection
     *
     * @return void
     */
    public function protect()
    {
        $where = $this->siteWide ? 'the system settings' : $this->url;

        \Log::error("A protect variable has been set in {$where} but was not able to be parsed. Access has been denied.");

        $this->deny();
    }
}
