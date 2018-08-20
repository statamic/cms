<?php

namespace Tests\Permissions;

use Tests\TestCase;
use Statamic\Data\Users\User;

class FilePermissibleTest extends TestCase
{
    use PermissibleContractTests;

    protected function createPermissible()
    {
        return new NonSavingEntry;
    }
}

class NonSavingEntry extends User
{
    public function save()
    {
        return $this;
    }
}
