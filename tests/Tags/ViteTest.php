<?php

namespace Tests\Tags;

use Illuminate\Foundation\Vite;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Parse;
use Tests\TestCase;

class ViteTest extends TestCase
{
    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);
        $app['config']->set('app.asset_url', 'http://localhost');
    }

    private function tag($tag, $data = [])
    {
        $this->withFakeVite();

        return (string) Parse::template($tag, $data);
    }

    #[Test]
    public function it_outputs_script()
    {
        $output = $this->tag('{{ vite src="test.js" }}');

        $expected = <<<'HTML'
<link rel="modulepreload" href="http://localhost/build/assets/test-123.js" />
<script type="module" src="http://localhost/build/assets/test-123.js"></script>
HTML;

        $this->assertEqualsIgnoringLineBreaks($expected, $output);
    }

    #[Test]
    public function it_outputs_stylesheet()
    {

        $output = $this->tag('{{ vite src="test.css" }}');

        $expected = <<<'HTML'
<link rel="preload" as="style" href="http://localhost/build/assets/test-123.css" />
<link rel="stylesheet" href="http://localhost/build/assets/test-123.css" />
HTML;

        $this->assertEqualsIgnoringLineBreaks($expected, $output);
    }

    #[Test]
    public function it_outputs_multiple_entry_points()
    {
        $output = $this->tag('{{ vite src="test.js|test.css" }}');

        $expected = <<<'HTML'
<link rel="preload" as="style" href="http://localhost/build/assets/test-123.css" />
<link rel="modulepreload" href="http://localhost/build/assets/test-123.js" />
<link rel="stylesheet" href="http://localhost/build/assets/test-123.css" />
<script type="module" src="http://localhost/build/assets/test-123.js"></script>
HTML;

        $this->assertEqualsIgnoringLineBreaks($expected, $output);
    }

    #[Test]
    public function it_includes_attributes()
    {
        $output = $this->tag('{{ vite src="test.js|test.css" alfa="bravo" attr:charlie="delta" }}');

        $expected = <<<'HTML'
<link rel="preload" as="style" href="http://localhost/build/assets/test-123.css" />
<link rel="modulepreload" href="http://localhost/build/assets/test-123.js" />
<link rel="stylesheet" href="http://localhost/build/assets/test-123.css" charlie="delta" />
<script type="module" src="http://localhost/build/assets/test-123.js" charlie="delta"></script>
HTML;

        $this->assertEqualsIgnoringLineBreaks($expected, $output);
    }

    #[Test]
    public function it_includes_tag_specific_attributes()
    {
        $output = $this->tag('{{ vite src="test.js|test.css" alfa="bravo" attr:charlie="delta" attr:script:echo="foxtrot" attr:style:golf="hotel" }}');

        $expected = <<<'HTML'
<link rel="preload" as="style" href="http://localhost/build/assets/test-123.css" />
<link rel="modulepreload" href="http://localhost/build/assets/test-123.js" />
<link rel="stylesheet" href="http://localhost/build/assets/test-123.css" charlie="delta" golf="hotel" />
<script type="module" src="http://localhost/build/assets/test-123.js" charlie="delta" echo="foxtrot"></script>
HTML;

        $this->assertEqualsIgnoringLineBreaks($expected, $output);
    }

    // Ignore line breaks just for the sake of readability in the test.
    private function assertEqualsIgnoringLineBreaks($expected, $actual)
    {
        $this->assertEquals(
            preg_replace('/\n+/', '', $expected),
            preg_replace('/\n+/', '', $actual)
        );
    }

    private function withFakeVite()
    {
        $this->swap(Vite::class, new class extends Vite
        {
            public function manifest($buildDirectory)
            {
                return [
                    'test.js' => [
                        'file' => 'assets/test-123.js',
                        'isEntry' => true,
                        'src' => 'test.js',
                    ],
                    'test.css' => [
                        'file' => 'assets/test-123.css',
                        'isEntry' => true,
                        'src' => 'test.css',
                    ],
                ];
            }
        });
    }
}
