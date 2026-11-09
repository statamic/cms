<?php

namespace Tests\Icons;

use Illuminate\Support\Facades\Facade;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Icon;
use Statamic\Icons\IconSet;
use Tests\TestCase;

class IconManagerTest extends TestCase
{
    #[Test]
    public function it_keeps_registered_sets_when_resolved_facade_instances_are_cleared()
    {
        Icon::register('test', statamic_path('resources/svg/icons'));

        Facade::clearResolvedInstances();

        $this->assertTrue(Icon::sets()->has('test'));
        $this->assertInstanceOf(IconSet::class, Icon::get('test'));
    }
}
