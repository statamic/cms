<?php

namespace Tests\Tags;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Parse;
use Tests\TestCase;

class IncrementTest extends TestCase
{
    protected $data = [
        'data' => [
            [
                'articles' => [
                    ['title' => 'One'],
                    ['title' => 'Two'],
                ],
            ],
            [
                'articles' => [
                    ['title' => 'Three'],
                    ['title' => 'Four'],
                ],
            ],
        ],
    ];

    private function tag($tag, $context = [])
    {
        return (string) Parse::template($tag, $context);
    }

    #[Test]
    public function basic_increment_works()
    {
        $template = <<<'EOT'
{{ data }}{{ articles }}{{ increment:test by="10" }}-{{ /articles }}{{ /data }}
EOT;

        $this->assertSame(
            '0-10-20-30-',
            $this->tag($template, $this->data)
        );
    }

    #[Test]
    public function increment_with_starting_value_works()
    {
        $template = <<<'EOT'
{{ data }}{{ articles }}{{ increment:test_two from="30" by="10" }}-{{ /articles }}{{ /data }}
EOT;

        $this->assertSame(
            '30-40-50-60-',
            $this->tag($template, $this->data)
        );
    }

    #[Test]
    public function resetting_an_increment_counter_with_a_value_resets_to_zero()
    {
        $template = <<<'EOT'
{{ data }}{{ increment:reset counter="test_three" }}{{ articles }}{{ title }}-{{ increment:test_three by="10" }}-{{ /articles }}{{ /data }}
EOT;

        $this->assertSame(
            'One-0-Two-10-Three-0-Four-10-',
            $this->tag($template, $this->data)
        );
    }

    #[Test]
    public function resetting_an_increment_counter_with_a_value_uses_the_new_starting_point()
    {
        $template = <<<'EOT'
{{ data }}{{ increment:reset counter="test_four" to="50" }}{{ articles }}{{ title }}-{{ increment:test_four by="10" }}-{{ /articles }}{{ /data }}
EOT;

        $this->assertSame(
            'One-60-Two-70-Three-60-Four-70-',
            $this->tag($template, $this->data)
        );
    }
}
