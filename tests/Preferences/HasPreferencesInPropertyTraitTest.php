<?php

namespace Tests\Preferences;

use Statamic\Preferences\HasPreferencesInProperty;
use Tests\TestCase;

class HasPreferencesInPropertyTraitTest extends TestCase
{
    use HasPreferencesTests;

    public function makeUser()
    {
        return new class
        {
            use HasPreferencesInProperty;
        };
    }
}
