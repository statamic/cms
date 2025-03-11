<?php

namespace Tests\Fields;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Exceptions\FieldtypeNotFoundException;
use Statamic\Fields\Fieldtype;
use Statamic\Fields\FieldtypeRepository;
use Tests\TestCase;

class FieldtypeRepositoryTest extends TestCase
{
    private $repo;

    public function setUp(): void
    {
        parent::setUp();

        $this->repo = new FieldtypeRepository;
    }

    #[Test]
    public function it_gets_a_fieldtype()
    {
        FooFieldtype::register();

        $found = $this->repo->find('test');
        $this->assertInstanceOf(FooFieldtype::class, $found);

        // Find it again and assert that it's a different instance each time.
        $second = $this->repo->find('test');
        $this->assertInstanceOf(FooFieldtype::class, $second);
        $this->assertNotSame($found, $second);
    }

    #[Test]
    public function it_caches_and_clones_existing_instances()
    {
        FooFieldtype::register();

        $found = $this->repo->find('test');
        $this->assertInstanceOf(FooFieldtype::class, $found);

        // Re-register another fieldtype that uses the same handle.
        // In reality this wouldn't happen, but we do it for this test to ensure the caching works.
        BarFieldtype::register();

        // Assert that it was registered. If you were to manually resolve it
        // out of the container you'd get the overridden fieldtype.
        $this->assertEquals(BarFieldtype::class, app('statamic.fieldtypes')->get('test'));

        // Find it again through the repo to assert that it's a different instance each time.
        $second = $this->repo->find('test');
        $this->assertInstanceOf(FooFieldtype::class, $second);
        $this->assertNotSame($found, $second);
    }

    #[Test]
    public function it_throw_exception_when_finding_invalid_fieldtype()
    {
        $this->expectException(FieldtypeNotFoundException::class);
        $this->expectExceptionMessage('Fieldtype [test] not found');
        $this->repo->find('test');
    }

    #[Test]
    public function it_makes_fields_selectable_in_forms()
    {
        $this->assertFalse($this->repo->hasBeenMadeSelectableInForms('test'));

        $this->repo->makeSelectableInForms('test');
        $this->assertTrue($this->repo->hasBeenMadeSelectableInForms('test'));
    }

    #[Test]
    public function it_makes_fields_unselectable_in_forms()
    {
        $this->repo->makeSelectableInForms('test');
        $this->assertTrue($this->repo->hasBeenMadeSelectableInForms('test'));

        $this->repo->makeUnselectableInForms('test');
        $this->assertFalse($this->repo->hasBeenMadeSelectableInForms('test'));
    }
}

class FooFieldtype extends Fieldtype
{
    protected static $handle = 'test';
}

class BarFieldtype extends Fieldtype
{
    protected static $handle = 'test';
}
