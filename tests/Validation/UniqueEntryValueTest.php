<?php

namespace Tests\Validation;

use Facades\Tests\Factories\EntryFactory;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Rules\UniqueEntryValue;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class UniqueEntryValueTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    #[Test]
    public function it_fails_when_theres_a_duplicate_entry_entry_value_in_across_all_collections()
    {
        EntryFactory::id('123')->slug('foo')->collection('collection-one')->create();
        EntryFactory::id('456')->slug('bar')->collection('collection-two')->create();

        $this->assertTrue(Validator::make(
            ['slug' => 'foo'],
            ['slug' => new UniqueEntryValue]
        )->fails());

        $this->assertTrue(Validator::make(
            ['slug' => 'baz'],
            ['slug' => new UniqueEntryValue]
        )->passes());
    }

    #[Test]
    public function it_fails_when_theres_a_duplicate_entry_entry_value_in_a_specific_collection()
    {
        EntryFactory::slug('foo')->collection('collection-one')->create();
        EntryFactory::slug('bar')->collection('collection-two')->create();

        $this->assertTrue(Validator::make(
            ['slug' => 'foo'],
            ['slug' => new UniqueEntryValue(collection: 'collection-one')]
        )->fails());

        $this->assertTrue(Validator::make(
            ['slug' => 'bar'],
            ['slug' => new UniqueEntryValue(collection: 'collection-one')]
        )->passes());
    }

    #[Test]
    public function it_passes_duplicate_slug_validation_when_updating_in_a_single_collection()
    {
        EntryFactory::id(123)->slug('foo')->collection('collection-one')->create();

        $this->assertTrue(Validator::make(
            ['slug' => 'foo'],
            ['slug' => new UniqueEntryValue(collection: 'collection-one', except: 123)]
        )->passes());

        $this->assertTrue(Validator::make(
            ['slug' => 'foo'],
            ['slug' => new UniqueEntryValue(collection: 'collection-one', except: 456)]
        )->fails());
    }

    #[Test]
    public function it_passes_when_theres_a_duplicate_entry_value_in_a_different_site()
    {
        $this->setSites([
            'site-one' => ['url' => '/', 'locale' => 'en_US'],
            'site-two' => ['url' => '/', 'locale' => 'fr_FR'],
        ]);

        EntryFactory::id(123)->slug('foo')->collection('collection-one')->locale('site-one')->create();

        $this->assertTrue(Validator::make(
            ['slug' => 'foo'],
            ['slug' => new UniqueEntryValue(collection: 'collection-one', site: 'site-one')]
        )->fails());

        $this->assertTrue(Validator::make(
            ['slug' => 'foo'],
            ['slug' => new UniqueEntryValue(collection: 'collection-one', site: 'site-two')]
        )->passes());
    }

    #[Test]
    public function it_fails_when_theres_a_duplicate_date_value()
    {
        EntryFactory::id('123')->slug('foo')->collection('collection-one')
            ->data(['test_date' => '2021-11-15 20:31:04'])
            ->create();

        $this->assertTrue(Validator::make(
            ['test_date' => '2021-11-15 20:31:04'],
            ['test_date' => new UniqueEntryValue]
        )->fails());
    }

    #[Test]
    public function it_fails_when_theres_a_duplicate_date_range_value()
    {
        EntryFactory::id('123')->slug('foo')->collection('collection-one')
            ->data(['test_date' => ['start' => '2021-11-14 09:00:00', 'end' => '2021-11-15 20:31:04']])
            ->create();

        $this->assertTrue(Validator::make(
            ['test_date' => ['start' => '2021-11-14 09:00:00', 'end' => '2021-11-15 20:31:04']],
            ['test_date' => new UniqueEntryValue]
        )->fails());
    }
}
