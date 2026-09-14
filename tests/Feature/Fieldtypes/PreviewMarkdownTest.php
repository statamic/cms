<?php

namespace Tests\Feature\Fieldtypes;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\User;
use Tests\FakesRoles;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class PreviewMarkdownTest extends TestCase
{
    use FakesRoles;
    use PreventSavingStacheItemsToDisk;

    private function request($payload)
    {
        return $this->postJson(cp_route('markdown.preview'), $payload);
    }

    #[Test]
    public function it_parses_markdown()
    {
        $this->setTestRoles(['test' => ['access cp']]);
        $user = User::make()->assignRole('test')->save();

        $this
            ->actingAs($user)
            ->request(['config' => ['type' => 'markdown'], 'value' => '**Hello**'])
            ->assertContent("<p><strong>Hello</strong></p>\n");
    }

    #[Test]
    public function it_aborts_for_non_markdown()
    {
        $this->setTestRoles(['test' => ['access cp']]);
        $user = User::make()->assignRole('test')->save();

        $this
            ->actingAs($user)
            ->request(['config' => ['type' => 'text'], 'value' => '**Hello**'])
            ->assertBadRequest()
            ->assertJson(['message' => 'Bad Request']);
    }

    #[Test]
    public function it_denies_access_without_control_panel_permission()
    {
        $this->setTestRoles(['test' => []]);
        $user = User::make()->assignRole('test')->save();

        $this
            ->actingAs($user)
            ->request(['config' => ['type' => 'markdown'], 'value' => '**Hello**'])
            ->assertForbidden();
    }
}
