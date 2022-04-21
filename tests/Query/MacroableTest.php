<?php

namespace Tests\Query;

use Tests\TestCase;
use Statamic\Facades\Term;
use Statamic\Facades\User;
use BadMethodCallException;
use Statamic\Facades\Entry;
use Tests\PreventSavingStacheItemsToDisk;

class MacroableTest extends TestCase
{
    /** @test **/
    public function it_can_add_custom_query_macro()
    {
        Entry::query()->macro('customEntryQuery', function () {
            return $this->where('foor', 'bar');
        });

        Term::query()->macro('customTermQuery', function () {
            return $this->where('foo', 'bar');
        });

        User::query()->macro('customUserQuery', function () {
            return $this->where('foo', 'bar');
        });

        try {
            Entry::query()->customEntryQuery();
            Term::query()->customTermQuery();
            User::query()->customUserQuery();
            $this->assertTrue(true);
        } catch (BadMethodCallException) {
            $this->assertTrue(false);
        }
    }

    /** @test **/
    public function it_ensures_that_the_custom_query_macros_are_scoped_to_the_individual_query_builder()
    {
        Entry::query()->macro('customEntryQuery', function () {
            return $this->where('foor', 'bar');
        });

        try {
            Term::query()->customEntryQuery();
            $this->assertTrue(false);
        } catch (BadMethodCallException) {
            $this->assertTrue(true);
        }
    }
}
