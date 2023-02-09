<?php

namespace Tests\Antlers\Runtime;

use Illuminate\Support\Facades\Log;
use Statamic\View\Antlers\Language\Utilities\StringUtilities;
use Tests\Antlers\ParserTestCase;

class NamedSlotsTest extends ParserTestCase
{
    public function test_named_slots_can_use_defaults()
    {
        $template = <<<'EOT'
{{ partial:card }}
Test description.
{{ /partial:card }}
EOT;

        $data = [
            'title' => 'Test Title',
        ];

        $expected = <<<'EOT'
<div class="max-w-sm rounded overflow-hidden shadow-lg">
    <img class="w-full" src="image/path.jpg" alt="Test Title">
    <div class="px-16___REPLACED py-8___REPLACED">
        <div class="font-bold text-xl mb-4___REPLACED">Test Title</div>
        <p class="text-gray-700 text-base">Test description.</p>
    </div>
    <div class="px-16___REPLACED pt-8___REPLACED pb-4___REPLACED">
        
        <span class="inline-block bg-gray-200 rounded-full px-6___REPLACED py-2___REPLACED text-sm font-semibold text-gray-700 mr-4___REPLACED mb-4___REPLACED">#tag1</span>
        <span class="inline-block bg-gray-200 rounded-full px-6___REPLACED py-2___REPLACED text-sm font-semibold text-gray-700 mr-4___REPLACED mb-4___REPLACED">#tag2</span>
        <span class="inline-block bg-gray-200 rounded-full px-6___REPLACED py-2___REPLACED text-sm font-semibold text-gray-700 mr-4___REPLACED mb-4___REPLACED">#tag3</span>
        
    </div>
</div>
EOT;

        $this->assertSame(StringUtilities::normalizeLineEndings($expected), $this->renderString($template, $data, true));
    }

    public function test_named_slots_can_override_defaults()
    {
        $template = <<<'EOT'
{{ partial:card }}
{{ slot:bottom }}
<span>I am the new content!</span>
{{ /slot:bottom }}

Test description.
{{ /partial:card }}
EOT;

        $data = [
            'title' => 'Test Title',
        ];

        $expected = <<<'EOT'
<div class="max-w-sm rounded overflow-hidden shadow-lg">
    <img class="w-full" src="image/path.jpg" alt="Test Title">
    <div class="px-16___REPLACED py-8___REPLACED">
        <div class="font-bold text-xl mb-4___REPLACED">Test Title</div>
        <p class="text-gray-700 text-base">Test description.</p>
    </div>
    <div class="px-16___REPLACED pt-8___REPLACED pb-4___REPLACED">
        
            <span>I am the new content!</span>
        
    </div>
</div>
EOT;

        $this->assertSame(StringUtilities::normalizeLineEndings($expected), $this->renderString($template, $data, true));
    }

    public function test_comments_are_ignored_when_checking_for_named_slots()
    {
        $template = <<<'EOT'
{{ partial src="prefixed" }}
 {{# comment #}}
 <{{ content }}>
 {{# comment #}}
{{ /partial }}
EOT;

        $this->assertSame('<The Content>', $this->renderString($template, ['content' => 'The Content'], true));
    }

    public function test_named_slots_do_not_end_up_in_the_log_as_loopable_variable_warnnig()
    {
        $template = <<<'EOT'
{{ partial:card }}
{{ slot:bottom }}
<span>I am the new content!</span>
{{ /slot:bottom }}

Test description.
{{ /partial:card }}
EOT;

        Log::shouldReceive('debug')->never();

        $this->renderString($template, [
            'title' => 'Test Title',
        ], true);
    }
}
