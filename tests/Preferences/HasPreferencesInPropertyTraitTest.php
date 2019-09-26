<?php

namespace Tests\Preferences;

use Tests\TestCase;
use Statamic\Preferences\HasPreferencesInProperty;

class HasPreferencesInPropertyTraitTest extends TestCase
{
    use HasPreferencesTests;

    function makeUser()
    {
        return new class {
            use HasPreferencesInProperty;
        };
    }
}