<?php

namespace Tests\Feature\CP;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\User;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class DashboardWidgetsControllerTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    #[Test]
    public function it_lists_available_widgets()
    {
        $user = User::make()->makeSuper()->save();

        $response = $this->actingAs($user)
            ->getJson(cp_route('dashboard.widgets.meta'))
            ->assertOk()
            ->json();

        $handles = collect($response)->pluck('handle')->all();

        $this->assertContains('collection', $handles);
        $this->assertContains('updater', $handles);
        $this->assertContains('template', $handles);

        $collection = collect($response)->firstWhere('handle', 'collection');
        $this->assertArrayHasKey('blueprint', $collection);
        $this->assertArrayHasKey('meta', $collection);
        $this->assertArrayHasKey('defaults', $collection);
        $this->assertArrayHasKey('icon', $collection);
        $this->assertArrayHasKey('title', $collection);
    }

    #[Test]
    public function it_requires_access_cp_for_meta()
    {
        $this->getJson(cp_route('dashboard.widgets.meta'))->assertUnauthorized();
    }

    #[Test]
    public function it_saves_widgets_to_user_preference()
    {
        $user = User::make()->makeSuper()->save();

        $payload = [
            'widgets' => [
                ['type' => 'collection', 'collection' => 'pages', 'limit' => 5],
                ['type' => 'updater'],
            ],
        ];

        $this->actingAs($user)
            ->patchJson(cp_route('dashboard.widgets.update'), $payload)
            ->assertNoContent();

        $fresh = $user->fresh();
        $saved = $fresh->getPreference('dashboard.widgets');

        $this->assertIsArray($saved);
        $this->assertCount(2, $saved);
        $this->assertEquals('collection', $saved[0]['type']);
        $this->assertEquals('updater', $saved[1]['type']);

        $this->assertEquals($saved, $fresh->preferences()['dashboard']['widgets']);
    }

    #[Test]
    public function it_processes_fieldtype_values_when_saving()
    {
        // The collections fieldtype (max_items: 1) sends an array from the frontend.
        // It should be processed to a string before being stored.
        $user = User::make()->makeSuper()->save();

        $this->actingAs($user)
            ->patchJson(cp_route('dashboard.widgets.update'), [
                'widgets' => [
                    ['type' => 'collection', 'collection' => ['pages']],
                ],
            ])
            ->assertNoContent();

        $saved = $user->fresh()->getPreference('dashboard.widgets');

        $this->assertEquals('pages', $saved[0]['collection']);
    }

    #[Test]
    public function it_filters_out_unknown_widget_types()
    {
        $user = User::make()->makeSuper()->save();

        $this->actingAs($user)
            ->patchJson(cp_route('dashboard.widgets.update'), [
                'widgets' => [
                    ['type' => 'collection'],
                    ['type' => 'i_do_not_exist'],
                ],
            ])
            ->assertNoContent();

        $saved = $user->fresh()->getPreference('dashboard.widgets');

        $this->assertCount(1, $saved);
        $this->assertEquals('collection', $saved[0]['type']);
    }

    #[Test]
    public function it_removes_widget_preference()
    {
        $user = User::make()->makeSuper()->preferences([
            'dashboard' => ['widgets' => [['type' => 'collection']]],
        ])->save();

        $this->assertNotNull($user->getPreference('dashboard.widgets'));

        $this->actingAs($user)
            ->deleteJson(cp_route('dashboard.widgets.destroy'))
            ->assertNoContent();

        $fresh = $user->fresh();

        $this->assertNull($fresh->getPreference('dashboard.widgets'));
        $this->assertArrayNotHasKey('dashboard', $fresh->preferences());
    }
}
