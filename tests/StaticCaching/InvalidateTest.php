<?php

namespace Tests\StaticCaching;

use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Events\BlueprintSaved;
use Statamic\Facades\Form;
use Statamic\StaticCaching\Invalidate;
use Statamic\StaticCaching\Invalidator;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class InvalidateTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    #[Test]
    public function it_invalidates_a_form_when_its_blueprint_is_saved()
    {
        $form = tap(Form::make('contact'))->save();

        $event = new BlueprintSaved($form->blueprint());

        $invalidator = Mockery::mock(Invalidator::class)->shouldReceive('invalidate')->once()->withArgs(function ($form) {
            return $form->handle() === 'contact';
        })->getMock();

        $invalidate = new Invalidate($invalidator);

        $invalidate->invalidateByBlueprint($event);
    }
}
