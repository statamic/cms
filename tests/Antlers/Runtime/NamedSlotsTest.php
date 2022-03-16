<?php

namespace Tests\Antlers\Runtime;

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
    <div class="px-6 py-4">
        <div class="font-bold text-xl mb-2">Test Title</div>
        <p class="text-gray-700 text-base">Test description.</p>
    </div>
    <div class="px-6 pt-4 pb-2">
        
        <span class="inline-block bg-gray-200 rounded-full px-3 py-1 text-sm font-semibold text-gray-700 mr-2 mb-2">#tag1</span>
        <span class="inline-block bg-gray-200 rounded-full px-3 py-1 text-sm font-semibold text-gray-700 mr-2 mb-2">#tag2</span>
        <span class="inline-block bg-gray-200 rounded-full px-3 py-1 text-sm font-semibold text-gray-700 mr-2 mb-2">#tag3</span>
        
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
    <div class="px-6 py-4">
        <div class="font-bold text-xl mb-2">Test Title</div>
        <p class="text-gray-700 text-base">Test description.</p>
    </div>
    <div class="px-6 pt-4 pb-2">
        
            <span>I am the new content!</span>
        
    </div>
</div>
EOT;

        $this->assertSame(StringUtilities::normalizeLineEndings($expected), $this->renderString($template, $data, true));
    }
}
