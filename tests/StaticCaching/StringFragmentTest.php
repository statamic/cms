<?php

namespace Tests\StaticCaching;

use PHPUnit\Framework\Attributes\Test;
use Statamic\StaticCaching\NoCache\StringFragment;
use Tests\TestCase;

class StringFragmentTest extends TestCase
{
    #[Test]
    public function it_writes_temporary_views_to_statamic_storage()
    {
        $compiledViewDirectory = storage_path('framework/views/string-fragment-'.uniqid());

        config()->set('view.compiled', $compiledViewDirectory);

        try {
            $fragment = new StringFragment('test-region', 'Hello {{ name }}', 'antlers.html', ['name' => 'World']);

            $this->assertSame('Hello World', $fragment->render());
            $this->assertDirectoryExists(storage_path('statamic/tmp/nocache'));
            $this->assertDirectoryDoesNotExist($compiledViewDirectory.'/nocache');
        } finally {
            app('files')->deleteDirectory($compiledViewDirectory);
        }
    }
}
