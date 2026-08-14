<?php

namespace Tests\Search;

use Illuminate\Support\Facades\Bus;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Search\Index;
use Statamic\Search\InsertMultipleJob;

trait IndexTests
{
    public function tearDown(): void
    {
        // Reset the static state of the Index class
        Index::resolveNameUsing(null);

        parent::tearDown();
    }

    abstract public function getIndexClass();

    public function getIndex($name, $config, $locale)
    {
        $class = $this->getIndexClass();

        return new $class($name, $config, $locale);
    }

    #[Test, DataProvider('nameProvider')]
    public function it_can_get_the_name($name, $config, $locale, $resolver, $expected)
    {
        if ($resolver) {
            $this->getIndexClass()::resolveNameUsing($resolver);
        }

        $index = $this->getIndex($name, $config, $locale);

        $this->assertEquals($expected, $index->name());
    }

    public static function nameProvider()
    {
        return [
            'basic' => ['test', [], null, null, 'test'],
            'with locale' => ['test', [], 'en', null, 'test_en'],
            'resolver' => ['test', [], null, fn ($name, $locale) => 'prefix_'.$name.'_'.$locale, 'prefix_test_'],
            'resolver with locale' => ['test', [], 'en', fn ($name, $locale) => 'prefix_'.$name.'_'.$locale, 'prefix_test_en'],
        ];
    }

    #[Test]
    public function it_dispatches_the_insert_job_with_the_configured_handle_not_the_resolved_name()
    {
        Bus::fake();

        // A custom resolver that prefixes the name. The resolved name can no longer be
        // reversed back into the configured handle by string manipulation.
        $this->getIndexClass()::resolveNameUsing(fn ($name, $locale) => 'prefix_'.$name.'_'.$locale);

        $index = $this->getIndex('test', [], 'en');
        $this->assertEquals('prefix_test_en', $index->name());

        $index->insertMultiple(collect(['foo::bar']));

        // The job re-resolves the index via Search::index($name, $locale), so it must
        // receive the configured handle ('test'), not the resolved name ('prefix_test_en').
        Bus::assertDispatched(
            InsertMultipleJob::class,
            fn (InsertMultipleJob $job) => $job->name === 'test' && $job->locale === 'en'
        );
    }
}
