<?php

namespace Tests\Forms;

use PHPUnit\Framework\Attributes\Test;
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

    #[Test]
    public function test_find_or_fail_gets_form()
    {
        $this->repo->make('test_form')->title('Test')->save();

        $form = $this->repo->findOrFail('test_form');

        $this->assertInstanceOf(Form::class, $form);
        $this->assertEquals('Test', $form->title());
    }

    #[Test]
    public function test_find_or_fail_throws_exception_when_form_does_not_exist()
    {
        $this->expectException(FormNotFoundException::class);
        $this->expectExceptionMessage('Form [does-not-exist] not found');

        $this->repo->findOrFail('does-not-exist');
    }

    /** @test */
    public function it_registers_config()
    {
        $this->repo->appendConfigFields('test_form', 'Test Config', [
            'another_config' => [
                'handle' => 'another_config',
                'field' => [
                    'type' => 'text',
                ],
            ],
            'some_config' => [
                'handle' => 'some_config',
                'field' => [
                    'type' => 'text',
                ],
            ],
        ]);

        $this->assertNotNull($this->repo->extraConfigFor('test_form'));
        $this->assertEmpty($this->repo->extraConfigFor('another_form'));
    }

    /** @test */
    public function it_registers_wildcard_config()
    {
        $this->repo->appendConfigFields('*', 'Test Config', [
            'another_config' => [
                'handle' => 'another_config',
                'field' => [
                    'type' => 'text',
                ],
            ],
            'some_config' => [
                'handle' => 'some_config',
                'field' => [
                    'type' => 'text',
                ],
            ],
        ]);

        $this->assertNotNull($this->repo->extraConfigFor('test_form'));
        $this->assertNotNull($this->repo->extraConfigFor('another_form'));
    }
}
