<?php

namespace Tests\View\Antlers;

use PHPUnit\Framework\Attributes\Test;
use Statamic\View\View;
use Tests\TestCase;

class BladeLayoutTest extends TestCase
{
    protected function getEnvironmentSetUp($app)
    {
        $currentViews = $app['config']->get('view.paths');

        $currentViews[] = __DIR__.'/fixtures/';

        $app['config']->set('view.paths', $currentViews);
    }

    #[Test]
    public function no_parse_extractions_are_replaced_when_extending_a_blade_layout()
    {
        $view = (new View)
            ->template('blade_injection')
            ->layout('blade_layout')
            ->with(['title' => 'The Title'])->render();

        $this->assertStringNotContainsString('noparse_', $view);
        $this->assertStringContainsString('The Title', $view);
        $this->assertStringContainsString('{{ title }}', $view);
    }
}
