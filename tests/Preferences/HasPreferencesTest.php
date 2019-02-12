<?php

namespace Tests\Preferences;

use Tests\TestCase;
use Statamic\Preferences\HasPreferences;

class HasPreferencesTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->person = new Person;
    }

    /** @test */
    function it_can_get_and_set_array_of_preferences()
    {
        $preferences = ['language' => 'english'];

        $this->assertEquals([], $this->person->preferences());

        $this->person->preferences($preferences);

        $this->assertEquals($preferences, $this->person->preferences());
    }

    /** @test */
    function it_can_add_array_of_preferences()
    {
        $this->person->preferences([
            'language' => 'english',
            'color' => 'red'
        ]);

        $this->person->addPreferences([
            'language' => 'french',
            'music' => 'metal'
        ]);

        $expected = [
            'language' => 'french',
            'color' => 'red',
            'music' => 'metal'
        ];

        $this->assertEquals($expected, $this->person->preferences());
    }

    /** @test */
    function it_can_add_a_single_preference()
    {
        $this->person->addPreference('collection.columns', ['date', 'title']);

        $expected = [
            'collection' => [
                'columns' => [
                    'date',
                    'title'
                ]
            ]
        ];

        $this->assertEquals($expected, $this->person->preferences());
    }

    /** @test */
    function it_can_remove_a_single_preference()
    {
        $this->person->preferences([
            'collection' => [
                'columns' => [
                    'date',
                    'title'
                ],
                'filters' => [
                    'published'
                ]
            ]
        ]);

        $this->person->removePreference('collection.columns');

        $expected = [
            'collection' => [
                'filters' => [
                    'published'
                ]
            ]
        ];

        $this->assertEquals($expected, $this->person->preferences());
    }

    /** @test */
    function it_can_get_a_single_preference()
    {
        $this->person->preferences([
            'collection' => [
                'filters' => [
                    'published'
                ]
            ]
        ]);

        $this->assertEquals(['filters' => ['published']], $this->person->getPreference('collection'));
        $this->assertEquals(['published'], $this->person->getPreference('collection.filters'));
        $this->assertEquals(null, $this->person->getPreference('language'));
    }

    /** @test */
    function it_can_check_if_a_single_preference_exists()
    {
        $this->person->preferences([
            'collection' => [
                'filters' => [
                    'published'
                ]
            ]
        ]);

        $this->assertTrue($this->person->hasPreference('collection'));
        $this->assertTrue($this->person->hasPreference('collection.filters'));
        $this->assertFalse($this->person->hasPreference('language'));
    }
}

class Person
{
    use HasPreferences;
}
