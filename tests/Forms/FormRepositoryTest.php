<?php

namespace Tests\Forms;

use Statamic\Contracts\Forms\Form;
use Statamic\Exceptions\FormNotFoundException;
use Statamic\Forms\FormRepository;
use Statamic\Stache\Stache;
use Tests\TestCase;

class FormRepositoryTest extends TestCase
{
    private $repo;

    public function setUp(): void
    {
        parent::setUp();

        $stache = (new Stache)->sites(['en']);
        $this->app->instance(Stache::class, $stache);

        $this->repo = new FormRepository($stache);
    }

    /** @test */
    public function test_find_or_fail_gets_form()
    {
        $this->repo->make('test_form')->title('Test')->save();

        $form = $this->repo->findOrFail('test_form');

        $this->assertInstanceOf(Form::class, $form);
        $this->assertEquals('Test', $form->title());
    }

    /** @test */
    public function test_find_or_fail_throws_exception_when_form_does_not_exist()
    {
        $this->expectException(FormNotFoundException::class);
        $this->expectExceptionMessage('Form [does-not-exist] not found');

        $this->repo->findOrFail('does-not-exist');
    }
}
