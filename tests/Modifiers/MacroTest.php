<?php

namespace Tests\Modifiers;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Modifiers\Modify;
use Tests\TestCase;

#[Group('array')]
class MacroTest extends TestCase
{
    #[Test]
    public function it_reads_a_macro_from_file_and_applies_it_on_a_string(): void
    {
        $basePath = $this->app->basePath();
        $this->app->setBasePath(__DIR__.'/../__fixtures__/modifiers');

        $input = "Actually i don't know what we're talking about.";
        $context = [];
        $expected = "Actually I Don't Know What We're Talking&nbsp;About";
        $modified = $this->modify($input, ['headline'], $context);
        $this->assertEquals($expected, $modified);

        $this->app->setBasePath($basePath);
    }

    #[Test]
    public function it_handles_a_macro_with_multiple_parameters(): void
    {
        $basePath = $this->app->basePath();
        $this->app->setBasePath(__DIR__.'/../__fixtures__/modifiers');

        $content = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. At in tellus integer feugiat scelerisque. Cras fermentum odio eu feugiat pretium nibh ipsum consequat. In nibh mauris cursus mattis. Elit at imperdiet dui accumsan sit amet nulla facilisi. Vel orci porta non pulvinar neque laoreet suspendisse. Aliquam ut porttitor leo a diam sollicitudin. Tincidunt tortor aliquam nulla facilisi. Mauris augue neque gravida in fermentum et. Velit egestas dui id ornare arcu.';
        $context = [];
        $expected = 'Lorem ipsum dolor sit...';
        $modified = $this->modify($content, ['excerpt'], $context);
        $this->assertEquals($expected, $modified);

        $this->app->setBasePath($basePath);
    }

    private function modify($value, array $params, array $context)
    {
        return Modify::value($value)->context($context)->macro($params)->fetch();
    }
}
