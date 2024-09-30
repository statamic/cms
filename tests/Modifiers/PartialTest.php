<?php

namespace Tests\Modifiers;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Modifiers\Modify;
use Tests\TestCase;

#[Group('array')]
class PartialTest extends TestCase
{
    #[Test]
    public function it_injects_variable_data_into_a_partial_and_renders_it(): void
    {
        $this->app->bind('filesystems.paths.resources', function () {
            return __DIR__.'/../__fixtures__/modifiers/resources/';
        });

        $data = [
            'title' => 'Bubble Guppies',
            'content' => 'Science died a little bit today.',
        ];

        $expected = "<h1>Bubble Guppies</h1>\n<p>Science died a little bit today.</p>\n\n";

        $modified = $this->modify($data, ['demo'], []);
        $this->assertStringEqualsStringIgnoringLineEndings($expected, (string) $modified);
    }

    private function modify($value, array $params, array $context)
    {
        return Modify::value($value)->context($context)->partial($params)->fetch();
    }
}
