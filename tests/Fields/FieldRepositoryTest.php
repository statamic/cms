<?php

namespace Tests\Fields;

use Tests\TestCase;
use Statamic\Fields\Field;
use Statamic\Fields\FieldRepository;
use Illuminate\Filesystem\Filesystem;

class FieldRepositoryTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->tempDir = __DIR__.'/tmp';
        mkdir($this->tempDir);

        $this->repo = app(FieldRepository::class)->setDirectory($this->tempDir);
    }

    public function tearDown()
    {
        (new Filesystem)->deleteDirectory($this->tempDir);
    }

    /** @test */
    function it_gets_a_field_within_a_fieldset()
    {
        $contents = <<<'EOT'
title: Test
fields:
  one:
    type: text
    display: First Field
EOT;
        file_put_contents($this->tempDir.'/test.yaml', $contents);

        $field = $this->repo->find('test.one');

        $this->assertInstanceOf(Field::class, $field);
        $this->assertEquals('one', $field->handle());
        $this->assertEquals('First Field', $field->display());

        // Valid fieldset but invalid field returns null
        $this->assertNull($this->repo->find('test.unknown'));
    }

    /** @test */
    function it_returns_null_if_fieldset_doesnt_exist()
    {
        $this->assertNull($this->repo->find('unknown.test'));
    }

    /** @test */
    function it_returns_null_if_fieldset_and_field_are_not_both_provided()
    {
        $this->assertNull($this->repo->find('test'));
    }
}
