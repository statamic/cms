<?php

namespace Tests\CP\Utilities;

use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\Test;
use Statamic\CP\Utilities\Utility;
use Statamic\CP\Utilities\UtilityRepository;
use Tests\TestCase;

class UtilityRepositoryTest extends TestCase
{
    #[Test]
    public function it_registers_a_utility()
    {
        $utilities = new UtilityRepository;
        $this->assertInstanceOf(Collection::class, $utilities->all());
        $this->assertCount(0, $utilities->all());

        $utility = $utilities->make('one');
        $this->assertCount(0, $utilities->all());

        $utilities->register($utility);
        $this->assertEquals(['one' => $utility], $utilities->all()->all());
        $this->assertEquals($utility, $utilities->find('one'));
    }

    #[Test]
    public function it_registers_a_utility_via_a_string()
    {
        $utilities = new UtilityRepository;

        $utility = $utilities->register('one');

        $this->assertInstanceOf(Utility::class, $utility);
        $this->assertEquals('one', $utility->handle());
        $this->assertCount(1, $utilities->all());
        $this->assertEquals(['one' => $utility], $utilities->all()->all());
    }

    #[Test]
    public function it_defers_registration_until_boot_using_extend_method()
    {
        $utilities = new UtilityRepository;
        $callbackRan = false;

        $utilities->extend(function ($arg) use ($utilities, &$callbackRan) {
            $this->assertEquals($utilities, $arg);
            $callbackRan = true;
        });

        $this->assertFalse($callbackRan);

        $utilities->boot();

        $this->assertTrue($callbackRan);
    }

    #[Test]
    public function booting_more_than_once_just_updates_the_utilities()
    {
        // This makes sure that booting a second time doesn't duplicate
        // any utilities. It should just update/replace the existing ones.
        // We boot once early so that routes can get registered, and
        // then again after the user's locale preference is set so
        // that the translations for labels etc use the right language.

        $utilities = new UtilityRepository;

        $utilities->extend(function ($utilities) {
            $utilities->register('test')
                ->title(__('and')); // using a translation that will likely never change.
        });

        $utilities->boot();

        $this->assertEquals(['and'], $utilities->all()->map->title()->values()->all());

        app()->setLocale('fr');

        $utilities->boot();

        $this->assertEquals(['et'], $utilities->all()->map->title()->values()->all());
    }
}
