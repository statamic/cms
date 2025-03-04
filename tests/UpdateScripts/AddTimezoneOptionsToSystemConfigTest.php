<?php

namespace Tests\UpdateScripts;

use Illuminate\Support\Facades\File;
use PHPUnit\Framework\Attributes\Test;
use Statamic\UpdateScripts\AddTimezoneOptionsToSystemConfig;
use Tests\TestCase;
use Tests\UpdateScripts\Concerns\RunsUpdateScripts;

class AddTimezoneOptionsToSystemConfigTest extends TestCase
{
    use RunsUpdateScripts;

    #[Test]
    public function it_is_registered()
    {
        $this->assertUpdateScriptRegistered(AddTimezoneOptionsToSystemConfig::class);
    }

    #[Test]
    public function it_appends_timezone_option_to_system_config()
    {
        config()->set('app.timezone', 'America/New_York'); // -05:00

        File::ensureDirectoryExists(app()->configPath('statamic'));

        File::put(app()->configPath('statamic/system.php'), <<<'EOT'
<?php

return [

    'above' => 'this',

    'date_format' => 'F jS, Y',

    'below' => 'that',

];
EOT
        );

        $this->runUpdateScript(AddTimezoneOptionsToSystemConfig::class);

        $systemConfig = File::get(app()->configPath('statamic/system.php'));

        $this->assertStringContainsString("'above' => 'this',", $systemConfig);
        $this->assertStringContainsString("'date_format' => 'F jS, Y',", $systemConfig);
        $this->assertStringContainsString("'display_timezone' => 'America/New_York',", $systemConfig);
        $this->assertStringContainsString("'localize_dates_in_modifiers' => true,", $systemConfig);
        $this->assertStringContainsString("'below' => 'that',", $systemConfig);
    }
}
