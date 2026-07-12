<?php

namespace Tests\Console\Commands;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SiteClearTest extends TestCase
{
    #[Test]
    public function it_has_up_to_date_config_stubs()
    {
        // site:clear overwrites the published stache.php and users.php with these
        // stubs (see SiteClear::resetStatamicConfigs), copying them verbatim, so
        // they need to keep the same defaults as a fresh Statamic install.
        $stubs = statamic_path('src/Console/Commands/stubs/config');

        $stache = require $stubs.'/stache.php.stub';

        $this->assertArrayHasKey('watcher', $stache);
        $this->assertArrayHasKey('cache_store', $stache);
        $this->assertArrayHasKey('warming', $stache);
        $this->assertArrayHasKey('collection-trees', $stache['stores']);
        $this->assertArrayHasKey('nav-trees', $stache['stores']);
        $this->assertArrayHasKey('global-variables', $stache['stores']);
        $this->assertArrayHasKey('assets', $stache['stores']);
        $this->assertArrayHasKey('form-submissions', $stache['stores']);
        $this->assertArrayHasKey('revisions', $stache['stores']);

        $users = require $stubs.'/users.php.stub';

        $this->assertSame('eloquent', $users['repository']);
        $this->assertArrayHasKey('registration_form_honeypot_field', $users);
        $this->assertArrayHasKey('wizard_invitation', $users);
        $this->assertArrayHasKey('roles', $users['tables']);
        $this->assertArrayHasKey('groups', $users['tables']);
        $this->assertArrayHasKey('webauthn', $users['tables']);
        $this->assertArrayHasKey('impersonate', $users);
        $this->assertArrayHasKey('elevated_sessions_enabled', $users);
        $this->assertArrayHasKey('elevated_session_duration', $users);
        $this->assertArrayHasKey('two_factor_enabled', $users);
        $this->assertArrayHasKey('two_factor_enforced_roles', $users);
        $this->assertArrayHasKey('sort_field', $users);
    }
}
