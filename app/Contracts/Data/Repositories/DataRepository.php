<?php

namespace Statamic\Contracts\Data\Repositories;

interface DataRepository
{
    public function find($reference);
    public function findByUri($uri, $site = null);
    public function splitReference($reference);
}