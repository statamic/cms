<?php

namespace Tests\Feature\Users;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\User;
use Tests\Auth\UnsafeEmailPayloads;
use Tests\FakesRoles;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class StoreUserTest extends TestCase
{
    use FakesRoles;
    use PreventSavingStacheItemsToDisk;
    use UnsafeEmailPayloads;

    #[Test]
    #[DataProvider('unsafeEmailProvider')]
    public function it_rejects_emails_that_are_unsafe_as_file_paths($email)
    {
        $this->setTestRoles(['test' => ['access cp', 'create users']]);
        $me = tap(User::make()->email('admin@domain.com')->assignRole('test'))->save();
        $before = $this->filesystemSnapshot();

        $this
            ->actingAs($me)
            ->postJson(cp_route('users.store'), ['email' => $email])
            ->assertJsonValidationErrors('email');

        $this->assertSame($before, $this->filesystemSnapshot());
    }
}
