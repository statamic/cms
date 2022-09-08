<?php

namespace Tests\CP\Navigation;

use Statamic\CP\Navigation\Nav;
use Statamic\Facades;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class NavPreferencesTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    protected $shouldPreventNavBeingBuilt = false;

    /** @test */
    public function it_can_reorder_sections()
    {
        $defaultSections = ['Top Level', 'Content', 'Fields', 'Tools', 'Users'];

        $this->assertEquals($defaultSections, $this->buildDefaultNav()->keys()->all());

        $reorderedSections = ['Top Level', 'Users', 'Fields', 'Content', 'Tools'];

        // Recommended syntax...
        $this->assertEquals($reorderedSections, $this->buildNavWithPreferences([
            'reorder' => true,
            'sections' => [
                'top_level' => '@inherit',
                'users' => '@inherit',
                'fields' => '@inherit',
                'content' => '@inherit',
                'tools' => '@inherit',
            ],
        ])->keys()->all());

        // Without nesting sections...
        $this->assertEquals($reorderedSections, $this->buildNavWithPreferences([
            'reorder' => true,
            'top_level' => '@inherit',
            'users' => '@inherit',
            'fields' => '@inherit',
            'content' => '@inherit',
            'tools' => '@inherit',
        ])->keys()->all());

        // Merge unmentioned sections underneath...
        $this->assertEquals($reorderedSections, $this->buildNavWithPreferences([
            'reorder' => true,
            'sections' => [
                'top_level' => '@inherit',
                'users' => '@inherit',
                'fields' => '@inherit',
            ],
        ])->keys()->all());

        // Merge top level section at top...
        $this->assertEquals($reorderedSections, $this->buildNavWithPreferences([
            'reorder' => true,
            'sections' => [
                'users' => '@inherit',
                'fields' => '@inherit',
            ],
        ])->keys()->all());

        // Always merge top level section at top, even when explicitly defining in middle...
        $this->assertEquals($reorderedSections, $this->buildNavWithPreferences([
            'reorder' => true,
            'sections' => [
                'users' => '@inherit',
                'top_level' => '@inherit',
                'fields' => '@inherit',
            ],
        ])->keys()->all());

        // Ensure re-ordering sections still works when modifying a section...
        $this->assertEquals($reorderedSections, $this->buildNavWithPreferences([
            'reorder' => true,
            'sections' => [
                'users' => '@inherit',
                'fields' => [
                    'items' => [
                        'top_level::dashboard' => '@alias',
                    ],
                ],
                'content' => '@inherit',
            ],
        ])->keys()->all());

        // If `reorder: false`, it should just use default section order...
        $this->assertEquals($defaultSections, $this->buildNavWithPreferences([
            'reorder' => false,
            'sections' => [
                'top_level' => '@inherit',
                'users' => '@inherit',
                'fields' => '@inherit',
                'content' => '@inherit',
                'tools' => '@inherit',
            ],
        ])->keys()->all());

        // If `reorder` is not specified, it should just use default item order...
        $this->assertEquals($defaultSections, $this->buildNavWithPreferences([
            'sections' => [
                'top_level' => '@inherit',
                'users' => '@inherit',
                'fields' => '@inherit',
                'content' => '@inherit',
                'tools' => '@inherit',
            ],
        ])->keys()->all());
    }

    private function buildNavWithPreferences($preferences)
    {
        // Swap with fakes instead of using mocks,
        // because a mock can only set one set of expectations per test method...
        Facades\Preference::swap(new FakePreferences($preferences));
        Facades\CP\Nav::swap(new Nav);

        $this->actingAs(tap(Facades\User::make()->makeSuper())->save());

        return Facades\CP\Nav::build();
    }

    private function buildDefaultNav()
    {
        return $this->buildNavWithPreferences([]);
    }
}

class FakePreferences
{
    private $preferences;

    public function __construct($preferences)
    {
        $this->preferences = $preferences;
    }

    public function get()
    {
        return $this->preferences;
    }
}
