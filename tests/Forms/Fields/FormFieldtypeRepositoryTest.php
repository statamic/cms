<?php

namespace Tests\Forms\Fields;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Exceptions\FormFieldtypeNotFoundException;
use Statamic\Forms\Fields\FormFieldtype;
use Statamic\Forms\Fields\FormFieldtypeRepository;
use Statamic\Forms\Fieldtypes\Fallback;
use Tests\TestCase;

class FormFieldtypeRepositoryTest extends TestCase
{
    private $repo;

    public function setUp(): void
    {
        parent::setUp();

        $this->repo = new FormFieldtypeRepository();
    }

    #[Test]
    public function it_gets_a_form_fieldtype()
    {
        FooFormFieldtype::register();

        $found = $this->repo->find('test');
        $this->assertInstanceOf(FooFormFieldtype::class, $found);

        // Find it again and assert that it's a different instance each time.
        $second = $this->repo->find('test');
        $this->assertInstanceOf(FooFormFieldtype::class, $second);
        $this->assertNotSame($found, $second);
    }

    #[Test]
    public function it_caches_and_clones_existing_instances()
    {
        FooFormFieldtype::register();

        $found = $this->repo->find('test');
        $this->assertInstanceOf(FooFormFieldtype::class, $found);

        // Re-register another fieldtype that uses the same handle.
        // In reality this wouldn't happen, but we do it for this test to ensure the caching works.
        BarFormFieldtype::register();

        // Assert that it was registered. If you were to manually resolve it
        // out of the container you'd get the overridden fieldtype.
        $this->assertEquals(BarFormFieldtype::class, app('statamic.form-fieldtypes')->get('test'));

        // Find it again through the repo to assert that it's a different instance each time.
        $second = $this->repo->find('test');
        $this->assertInstanceOf(FooFormFieldtype::class, $second);
        $this->assertNotSame($found, $second);
    }

    #[Test]
    public function it_returns_fallback_fieldtype_when_no_form_fieldtype_exists()
    {
        // "video" is a regular fieldtype, but not a form fieldtype.
        $found = $this->repo->find('video');

        $this->assertInstanceOf(Fallback::class, $found);
    }

    #[Test]
    public function it_throws_exception_when_form_fieldtype_cant_be_found()
    {
        $this->expectException(FormFieldtypeNotFoundException::class);
        $this->expectExceptionMessage('Form Fieldtype [test] not found');
        $this->repo->find('test');
    }

    #[Test]
    public function it_makes_fields_selectable_in_forms()
    {
        $this->assertFalse($this->repo->hasBeenMadeSelectable('test-selectable'));

        $this->repo->makeSelectable('test-selectable');
        $this->assertTrue($this->repo->hasBeenMadeSelectable('test-selectable'));
        $this->assertTrue($this->repo->selectableIsOverriden('test-selectable'));
    }

    #[Test]
    public function it_makes_fields_unselectable_in_forms()
    {
        $this->repo->makeSelectable('test-unselectable');
        $this->assertTrue($this->repo->hasBeenMadeSelectable('test-unselectable'));

        $this->repo->makeUnselectable('test-unselectable');
        $this->assertFalse($this->repo->hasBeenMadeSelectable('test-unselectable'));
        $this->assertTrue($this->repo->selectableIsOverriden('test-unselectable'));
    }
}

class FooFormFieldtype extends FormFieldtype
{
    public static $handle = 'test';

    public function toFieldArray(): array
    {
        return [];
    }
}

class BarFormFieldtype extends FormFieldtype
{
    public static $handle = 'test';

    public function toFieldArray(): array
    {
        return [];
    }
}
