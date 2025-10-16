<?php

namespace Tests\UpdateScripts;

use Illuminate\Support\Facades\File;
use PHPUnit\Framework\Attributes\Test;
use Statamic\UpdateScripts\AddTimezoneConfigOptions;
use Tests\TestCase;
use Tests\UpdateScripts\Concerns\RunsUpdateScripts;

class AddTimezoneConfigOptionsTest extends TestCase
{
    use RunsUpdateScripts;

    #[Test]
    public function it_is_registered()
    {
        $this->assertUpdateScriptRegistered(AddTimezoneConfigOptions::class);
    }

    #[Test]
    public function it_appends_timezone_option_to_system_config()
    {
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

        $this->runUpdateScript(AddTimezoneConfigOptions::class);

        $systemConfig = File::get(app()->configPath('statamic/system.php'));

        $this->assertStringContainsString("'above' => 'this',", $systemConfig);
        $this->assertStringContainsString("'date_format' => 'F jS, Y',", $systemConfig);
        $this->assertStringContainsString("'display_timezone' => null,", $systemConfig);
        $this->assertStringContainsString("'localize_dates_in_modifiers' => true,", $systemConfig);
        $this->assertStringContainsString("'below' => 'that',", $systemConfig);
    }
}
