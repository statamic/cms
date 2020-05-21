<?php

namespace Statamic\Licensing;

use Statamic\Facades\Addon;

class AddonLicense extends License
{
    protected $package;
    protected $addon;

    public function __construct($package, $response)
    {
        parent::__construct($response);
        $this->package = $package;
        $this->addon = Addon::get($package);
    }

    public function name()
    {
        return $this->addon->name();
    }

    public function version()
    {
        return $this->addon->version();
    }

    public function existsOnMarketplace()
    {
        return $this->response['exists'];
    }
}
