<?php

namespace Tests\Forms\Fields;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Exceptions\FormFieldtypeNotFoundException;
use Statamic\Forms\Fields\FormFieldtype;
use Statamic\Forms\Fields\FormFieldtypeRepository;
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
