<?php

namespace Tests\Fields;

use Statamic\Fields\FieldTransformer;
use Tests\TestCase;

class FieldTransformerTest extends TestCase
{
    protected function configToVue($config)
    {
        return FieldTransformer::toVue(['handle' => 'test', 'field' => $config])['config'];
    }

    /** @test */
    public function it_defaults_to_width_100()
    {
        // Will use configured width if set.
        $this->assertEquals(50, $this->configToVue(['width' => 50])['width']);

        // Defaults to width 100 if not set.
        $this->assertEquals(100, $this->configToVue([])['width']);
    }

    /** @test */
    public function it_defaults_to_localizable_false()
    {
        // Will use configured localizable if set.
        $this->assertTrue($this->configToVue(['localizable' => true])['localizable']);

        // Defaults to localizable false if not set.
        $this->assertFalse($this->configToVue([])['localizable']);
    }

    /** @test */
    public function it_normalizes_required_validation()
    {
        // It should replace `required: true` with `validate: ['required']`
        $this->assertArrayHasKey('validate', $config = $this->configToVue(['required' => true]));
        $this->assertArrayNotHasKey('required', $config = $this->configToVue(['required' => true]));
        $this->assertEquals(['required'], $config['validate']);

        // It should prepend `required`
        $this->assertEquals(
            ['required', 'email'],
            $this->configToVue(['required' => true, 'validate' => ['email']])['validate']
        );

        // It shouldn't prepend `required` if it already exists as a rule
        $this->assertEquals(
            ['min:3', 'required'],
            $this->configToVue(['required' => true, 'validate' => ['min:3', 'required']])['validate']
        );

        // It should normalize to an array and prepend `required`
        $this->assertEquals(
            ['required', 'min:3', 'email'],
            $this->configToVue(['required' => true, 'validate' => 'min:3|email'])['validate']
        );

        // It should normalize to an array but shouldn't prepend `required` if it already exists as a rule
        $this->assertEquals(
            ['min:3', 'required', 'email'],
            $this->configToVue(['required' => true, 'validate' => 'min:3|required|email'])['validate']
        );
    }
}
