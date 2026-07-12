<?php

namespace Statamic\StaticCaching\NoCache;

class RegionNotFound extends \Exception
{
    private $region;

    public function __construct($region)
    {
        $this->region = $region;
        parent::__construct("Region [{$region}] not found.");
    }

    public function getRegion()
    {
        return $this->region;
    }
}
