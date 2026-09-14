<?php

namespace Tests\Feature\Fieldtypes;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Blueprint as BlueprintFacade;
use Statamic\Facades\User;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class ReplicatorSetControllerAuthorizationTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    #[Test]
    public function it_allows_requests_with_a_valid_blueprint_token()
    {
        BlueprintFacade::partialMock();
        BlueprintFacade::shouldReceive('find')
            ->with('collections.pages.default')
            ->andReturn($this->makeBlueprint());

        $user = tap(User::make()->makeSuper())->save();

        $this
            ->actingAs($user)
            ->postJson(cp_route('replicator-fieldtype.set'), [
                'token' => encrypt([
                    'fqh' => 'collections.pages.default',
                    'user_id' => $user->id(),
                ]),
                'field' => 'content',
                'set' => 'text',
            ])
            ->assertOk()
            ->assertJson([
                'defaults' => [
                    'a_text_field' => 'the default',
                ],
            ]);
    }

    #[Test]
    public function it_requires_a_blueprint_token()
    {
        $user = tap(User::make()->makeSuper())->save();

        $this
            ->actingAs($user)
            ->postJson(cp_route('replicator-fieldtype.set'), [
                'field' => 'content',
                'set' => 'text',
            ])
            ->assertStatus(422);
    }

    #[Test]
    public function it_denies_requests_with_a_tampered_blueprint_token()
    {
        $user = tap(User::make()->makeSuper())->save();

        $this
            ->actingAs($user)
            ->postJson(cp_route('replicator-fieldtype.set'), [
                'token' => encrypt([
                    'fqh' => 'collections.pages.default',
                    'user_id' => $user->id(),
                ]).'tampered',
                'field' => 'content',
                'set' => 'text',
            ])
            ->assertForbidden();
    }

    #[Test]
    public function it_denies_requests_with_a_token_for_a_different_user()
    {
        $actingUser = tap(User::make()->makeSuper())->save();
        $otherUser = tap(User::make()->makeSuper())->save();

        $this
            ->actingAs($actingUser)
            ->postJson(cp_route('replicator-fieldtype.set'), [
                'token' => encrypt([
                    'fqh' => 'collections.pages.default',
                    'user_id' => $otherUser->id(),
                ]),
                'field' => 'content',
                'set' => 'text',
            ])
            ->assertForbidden();
    }

    #[Test]
    public function it_denies_requests_with_a_token_missing_the_blueprint_handle()
    {
        $user = tap(User::make()->makeSuper())->save();

        $this
            ->actingAs($user)
            ->postJson(cp_route('replicator-fieldtype.set'), [
                'token' => encrypt([
                    'user_id' => $user->id(),
                ]),
                'field' => 'content',
                'set' => 'text',
            ])
            ->assertForbidden();
    }

    private function makeBlueprint()
    {
        $blueprint = BlueprintFacade::make()->setHandle('default')->setNamespace('collections.pages');
        $blueprint->setContents([
            'sections' => [
                'main' => [
                    'fields' => [
                        [
                            'handle' => 'content',
                            'field' => [
                                'type' => 'replicator',
                                'sets' => [
                                    'main' => [
                                        'sets' => [
                                            'text' => [
                                                'fields' => [
                                                    [
                                                        'handle' => 'a_text_field',
                                                        'field' => [
                                                            'type' => 'text',
                                                            'default' => 'the default',
                                                        ],
                                                    ],
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        return $blueprint;
    }
}
