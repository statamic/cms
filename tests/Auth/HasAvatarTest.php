<?php

namespace Tests\Auth;

use Facades\Statamic\Fields\BlueprintRepository;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\AssetContainer;
use Statamic\Facades\Blueprint;
use Statamic\Facades\User;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class HasAvatarTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    public function setUp(): void
    {
        parent::setUp();

        Storage::fake('avatars', ['url' => '/avatars']);

        AssetContainer::make('avatars')->disk('avatars')->save();
    }

    private function withAvatarField()
    {
        $blueprint = Blueprint::makeFromFields(['avatar' => ['type' => 'assets', 'max_files' => 1]]);

        BlueprintRepository::shouldReceive('find')->with('user')->andReturn($blueprint);

        return $this;
    }

    private function withoutAvatarField()
    {
        // Nothing needs to be done, as there's no avatar field by default.
        // We just ship one in statamic/statamic
        return $this;
    }

    private function withGravatar()
    {
        config(['statamic.users.avatars' => 'gravatar']);

        return $this;
    }

    private function withoutGravatar()
    {
        config(['statamic.users.avatars' => 'initials']);

        return $this;
    }

    private function userWithUploadedAvatar()
    {
        Storage::disk('avatars')->putFileAs('', UploadedFile::fake()->image('john.jpg'), 'john.jpg');

        return $this->user()->set('avatar', 'john.jpg');
    }

    private function userWithoutUploadedAvatar()
    {
        return $this->user()->set('avatar', null);
    }

    private function user()
    {
        return User::make()->email('john@example.com');
    }

    #[Test]
    public function it_gets_the_avatar_if_theres_a_field_defined_in_the_blueprint_and_one_has_been_uploaded()
    {
        $user = $this->withAvatarField()->withGravatar()->userWithUploadedAvatar();

        $this->assertEquals('/avatars/john.jpg', $user->avatarFieldUrl());
        $this->assertEquals('/avatars/john.jpg', $user->avatarFieldUrl(64));
        $this->assertEquals('http://localhost/cp/thumbnails/YXZhdGFyczo6am9obi5qcGc=/small/square', $user->avatar());
        $this->assertEquals('http://localhost/cp/thumbnails/YXZhdGFyczo6am9obi5qcGc=/small/square', $user->avatar(64));
        $this->assertEquals('https://www.gravatar.com/avatar/d4c74594d841139328695756648b6bd6?s=64', $user->gravatarUrl());
        $this->assertEquals('https://www.gravatar.com/avatar/d4c74594d841139328695756648b6bd6?s=64', $user->gravatarUrl(64));
        $this->assertEquals('https://www.gravatar.com/avatar/d4c74594d841139328695756648b6bd6?s=128', $user->gravatarUrl(128));
    }

    /**
     * @see https://github.com/statamic/cms/issues/3207
     **/
    #[Test]
    public function it_gets_the_gravatar_if_theres_a_field_defined_in_the_blueprint_and_an_uploaded_asset_was_deleted()
    {
        $user = $this->withAvatarField()->withGravatar()->user()->set('avatar', 'john.jpg');

        $this->assertEquals('https://www.gravatar.com/avatar/d4c74594d841139328695756648b6bd6?s=64', $user->avatar());
        $this->assertEquals('https://www.gravatar.com/avatar/d4c74594d841139328695756648b6bd6?s=64', $user->avatar(64));
        $this->assertEquals('https://www.gravatar.com/avatar/d4c74594d841139328695756648b6bd6?s=128', $user->avatar(128));
        $this->assertEquals('https://www.gravatar.com/avatar/d4c74594d841139328695756648b6bd6?s=64', $user->gravatarUrl());
        $this->assertEquals('https://www.gravatar.com/avatar/d4c74594d841139328695756648b6bd6?s=64', $user->gravatarUrl(64));
        $this->assertEquals('https://www.gravatar.com/avatar/d4c74594d841139328695756648b6bd6?s=128', $user->gravatarUrl(128));
    }

    #[Test]
    public function it_gets_the_gravatar_if_theres_a_field_defined_but_nothing_has_been_uploaded()
    {
        $user = $this->withAvatarField()->withGravatar()->userWithoutUploadedAvatar();

        $this->assertEquals('https://www.gravatar.com/avatar/d4c74594d841139328695756648b6bd6?s=64', $user->avatar());
        $this->assertEquals('https://www.gravatar.com/avatar/d4c74594d841139328695756648b6bd6?s=64', $user->avatar(64));
        $this->assertEquals('https://www.gravatar.com/avatar/d4c74594d841139328695756648b6bd6?s=128', $user->avatar(128));
        $this->assertEquals('https://www.gravatar.com/avatar/d4c74594d841139328695756648b6bd6?s=64', $user->gravatarUrl());
        $this->assertEquals('https://www.gravatar.com/avatar/d4c74594d841139328695756648b6bd6?s=64', $user->gravatarUrl(64));
        $this->assertEquals('https://www.gravatar.com/avatar/d4c74594d841139328695756648b6bd6?s=128', $user->gravatarUrl(128));
    }

    #[Test]
    public function it_gets_the_gravatar_if_theres_an_avatar_value_but_not_a_field_in_the_blueprint()
    {
        $user = $this->withoutAvatarField()->withGravatar()->userWithUploadedAvatar();

        $this->assertEquals('https://www.gravatar.com/avatar/d4c74594d841139328695756648b6bd6?s=64', $user->avatar());
        $this->assertEquals('https://www.gravatar.com/avatar/d4c74594d841139328695756648b6bd6?s=64', $user->avatar(64));
        $this->assertEquals('https://www.gravatar.com/avatar/d4c74594d841139328695756648b6bd6?s=128', $user->avatar(128));
        $this->assertEquals('https://www.gravatar.com/avatar/d4c74594d841139328695756648b6bd6?s=64', $user->gravatarUrl());
        $this->assertEquals('https://www.gravatar.com/avatar/d4c74594d841139328695756648b6bd6?s=64', $user->gravatarUrl(64));
        $this->assertEquals('https://www.gravatar.com/avatar/d4c74594d841139328695756648b6bd6?s=128', $user->gravatarUrl(128));
    }

    #[Test]
    public function it_gets_null_if_theres_a_field_defined_but_nothing_has_been_uploaded_and_gravatar_is_disabled()
    {
        $user = $this->withAvatarField()->withoutGravatar()->userWithoutUploadedAvatar();

        $this->assertNull($user->avatar());
        $this->assertNull($user->gravatarUrl());
        $this->assertNull($user->gravatarUrl(64));
        $this->assertNull($user->gravatarUrl(128));
    }

    #[Test]
    public function it_gets_null_if_theres_an_avatar_value_but_not_a_field_in_the_blueprint_and_gravatar_is_disabled()
    {
        $user = $this->withoutAvatarField()->withoutGravatar()->userWithUploadedAvatar();

        $this->assertNull($user->avatar());
        $this->assertNull($user->avatar(64));
        $this->assertNull($user->avatar(128));
        $this->assertNull($user->gravatarUrl());
        $this->assertNull($user->gravatarUrl(64));
        $this->assertNull($user->gravatarUrl(128));
    }
}
