<?php

namespace Tests\Fields;

use Tests\TestCase;
use Statamic\Fields\Field;
use Statamic\Fields\Fieldset;
use Statamic\Fields\FieldRepository;
use Illuminate\Filesystem\Filesystem;
use Statamic\Fields\FieldsetRepository;

class FieldsetRepositoryTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->tempDir = __DIR__.'/tmp';
        mkdir($this->tempDir);

        $this->repo = app(FieldsetRepository::class)->setDirectory($this->tempDir);
    }

    public function tearDown()
    {
        (new Filesystem)->deleteDirectory($this->tempDir);
    }

    /** @test */
    function it_gets_a_fieldset()
    {
        $contents = <<<'EOT'
title: Test Fieldset
fields:
  one:
    type: text
    display: First Field
  two:
    type: text
    display: Second Field
EOT;
        file_put_contents($this->tempDir.'/test.yaml', $contents);

        $fieldset = $this->repo->find('test');

        $this->assertInstanceOf(Fieldset::class, $fieldset);
        $this->assertEquals('Test Fieldset', $fieldset->title());
        $this->assertEquals('test', $fieldset->handle());
        $this->assertEquals(['one', 'two'], $fieldset->fields()->map->handle()->values()->all());
        $this->assertEquals(['First Field', 'Second Field'], $fieldset->fields()->map->display()->values()->all());
    }

    /** @test */
    function it_returns_null_if_fieldset_doesnt_exist()
    {
        $this->assertNull($this->repo->find('unknown'));
    }
}
