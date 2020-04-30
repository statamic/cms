<?php

namespace Statamic\Contracts\Data;

interface DataRepository
{
    public function find($reference);

    public function findByUri($uri, $site = null);

    public function splitReference($reference);
}
