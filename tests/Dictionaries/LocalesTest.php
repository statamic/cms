<?php

namespace Tests\Dictionaries;

use Facades\Statamic\Console\Processes\Process;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Dictionaries\Locales;
use Tests\TestCase;

class LocalesTest extends TestCase
{
    #[Test]
    public function it_only_runs_the_locale_process_once_and_caches_the_result()
    {
        Process::shouldReceive('run')->once()->andReturn("en_US.UTF-8\nfr_FR.UTF-8\nC\nPOSIX");

        $expected = ['en_US' => 'en_US', 'fr_FR' => 'fr_FR'];

        $this->assertEquals($expected, (new Locales)->options());
        $this->assertEquals($expected, (new Locales)->options());
    }

    #[Test]
    public function cached_items_can_still_be_searched()
    {
        Process::shouldReceive('run')->once()->andReturn("en_US.UTF-8\nfr_FR.UTF-8");

        (new Locales)->options();

        $this->assertEquals(['fr_FR' => 'fr_FR'], (new Locales)->options('fr'));
    }
}
