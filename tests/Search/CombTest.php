<?php

namespace Tests\Search;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Search\Comb\Comb;
use Statamic\Search\Comb\Exceptions\NoResultsFound;
use Tests\TestCase;

class CombTest extends TestCase
{
    #[Test]
    #[DataProvider('searchesProvider')]
    public function it_searches($term, $expected)
    {
        $comb = new Comb([
            ['title' => 'John Doe', 'email' => 'john@doe.com'],
            ['title' => 'Jane Doe', 'email' => 'jane@doe.com'],
        ]);

        try {
            $results = $comb->lookUp($term);
        } catch (NoResultsFound $e) {
            $results = [];
        }

        $this->assertEquals($expected, collect($results['data'] ?? [])->pluck('data.title')->all());
    }

    #[Test]
    public function it_extracts_snippets()
    {
        $content = <<<'EOT'
I would choose pizza. I think. But how then would I stream Teenage Mutant Ninja Turtles?
Perhaps a wired ethernet connection to my Apple TV would be an acceptable loophole. Who
designed this question anyway? Did they mean to imply all pizza, or just wireless
pizza? Wifi is definitely more convenient but I could probably survive just fine
wiring my whole house with Cat5. Or Cat6. Or whatever the latest is, it's hard to pizza.
EOT;

        $comb = new Comb([
            ['content' => $content],
        ]);

        try {
            $results = $comb->lookUp('pizza');
        } catch (NoResultsFound $e) {
            $results = [];
        }

        $expected = [[
            'I would choose pizza. I think. But how then would I stream Teenage Mutant Ninja Turtles? Perhaps a',
            'question anyway? Did they mean to imply all pizza, or just wireless pizza? Wifi is definitely more',
            'just fine wiring my whole house with Cat5. Or Cat6. Or whatever the latest is, it\'s hard to pizza.',
        ]];

        $this->assertEquals($expected, collect($results['data'] ?? [])->pluck('snippets.content')->all());
    }

    #[Test]
    public function it_extracts_snippets_from_a_bard_field()
    {
        $content = [
            [
                'type' => 'heading',
                'attrs' => ['textAlign' => 'left', 'level' => 2],
                'content' => [['type' => 'text', 'text' => 'Bonbon ice cream chocolate pie']],
            ],
            [
                'type' => 'paragraph',
                'attrs' => ['textAlign' => 'left'],
                'content' => [
                    ['type' => 'text', 'text' => 'Candy canes ice cream '],
                    ['type' => 'text', 'marks' => [['type' => 'bold']], 'text' => 'chocolate '],
                    ['type' => 'text', 'text' => 'bar bear claw '],
                    ['type' => 'text', 'marks' => [['type' => 'italic']], 'text' => 'chocolate'],
                    ['type' => 'text', 'text' => ' oat cake powder sweet pudding. Candy canes croissant ma caroon dessert marzipan icing topping. Pastry caramels shortbread '],
                    [
                        'type' => 'text',
                        'marks' => [['type' => 'link', 'attrs' => ['href' => 'https://statamic.dev', 'rel' => null, 'target' => null, 'title' => null]]],
                        'text' => 'chocolate',
                    ],
                    ['type' => 'text', 'text' => ' jujubes chupa chups pudding.'],
                ],
            ],
            [
                'type' => 'bulletList',
                'content' => [
                    [
                        'type' => 'listItem',
                        'content' => [
                            [
                                'type' => 'paragraph',
                                'attrs' => ['textAlign' => 'left'],
                                'content' => [['type' => 'text', 'text' => 'Brownie liquorice jelly beans chocolate cake']],
                            ],
                        ],
                    ],
                ],
            ],
            [
                'type' => 'paragraph',
                'attrs' => ['textAlign' => 'left'],
                'content' => [['type' => 'text', 'text' => 'Cookie caramels ice cream liquorice bear claw. ']],
            ],
            [
                'type' => 'blockquote',
                'content' => [
                    [
                        'type' => 'paragraph',
                        'attrs' => ['textAlign' => 'left'],
                        'content' => [['type' => 'text', 'text' => 'Chocolate bar jelly-o lollipop powder carrot cake bonbon.']],
                    ],
                ],
            ],
            [
                'type' => 'table',
                'content' => [
                    [
                        'type' => 'tableRow',
                        'content' => [
                            [
                                'type' => 'tableCell',
                                'attrs' => ['colspan' => 1, 'rowspan' => 1, 'colwidth' => null],
                                'content' => [
                                    [
                                        'type' => 'paragraph',
                                        'attrs' => ['textAlign' => 'left'],
                                        'content' => [['type' => 'text', 'text' => 'chocolate']],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            [
                'type' => 'paragraph',
                'attrs' => ['textAlign' => 'center'],
                'content' => [
                    ['type' => 'text', 'text' => 'Liquorice '],
                    ['type' => 'text', 'marks' => [['type' => 'underline']], 'text' => 'chocolate'],
                    ['type' => 'text', 'text' => ' bar fruitcake cotton candy powder pie. Dragée bonbon pie shortbread danish cupcake. Ice cream lemon drops gummi bears jujubes macaroon tart chupa chups sugar plum ice cream. Marshmallow dragée cake jujubes fruitcake.'],
                ],
            ],
            [
                'type' => 'paragraph',
                'attrs' => ['textAlign' => 'right'],
                'content' => [
                    ['type' => 'text', 'text' => 'Marzipan danish tart '],
                    ['type' => 'text', 'marks' => [['type' => 'strike']], 'text' => 'chocolate'],
                    ['type' => 'text', 'text' => ' icing. '],
                    ['type' => 'text', 'marks' => [['type' => 'small']], 'text' => 'Chocolate'],
                    ['type' => 'text', 'text' => ' bar sugar plum gummies muffin pie. Jelly beans sweet sugar plum donut bear claw gummies cheesecake '],
                    ['type' => 'text', 'marks' => [['type' => 'superscript']], 'text' => 'chocolate'],
                    ['type' => 'text', 'text' => ' cake. Bonbon apple pie brownie liquorice icing muffin pie pastry.'],
                ],
            ],
            [
                'type' => 'paragraph',
                'attrs' => ['textAlign' => 'justify'],
                'content' => [
                    ['type' => 'text', 'text' => 'Tart sweet roll sesame snaps candy canes lemon drops '],
                    ['type' => 'text', 'marks' => [['type' => 'subscript']], 'text' => 'chocolate'],
                    ['type' => 'text', 'text' => '.'],
                ],
            ],
            [
                'type' => 'paragraph',
                'attrs' => ['textAlign' => 'left'],
                'content' => [['type' => 'text', 'marks' => [['type' => 'code']], 'text' => 'Cotton candy dragée apple pie chupa chups bear claw lollipop sugar plum fruitcake halvah. Oat cake jujubes powder chocolate cake cotton candy marzipan brownie.']],
            ],
            [
                'type' => 'codeBlock',
                'attrs' => ['language' => null],
                'content' => [['type' => 'text', 'text' => 'Apple pie pudding powder tart marzipan brownie marzipan. Lollipop halvah croissant sugar plum jelly beans fruitcake marshmallow ice cream cupcake. Jelly macaroon chocolate pudding croissant chocolate bar.']],
            ],
        ];

        $comb = new Comb([
            ['content' => $content],
        ]);

        try {
            $results = $comb->lookUp('chocolate');
        } catch (NoResultsFound $e) {
            $results = [];
        }

        $expected = [[
            'Bonbon ice cream chocolate pie Candy canes ice cream chocolate bar bear claw chocolate oat cake',
            'icing topping. Pastry caramels shortbread chocolate jujubes chupa chups pudding. Brownie liquorice',
            'caramels ice cream liquorice bear claw. Chocolate bar jelly-o lollipop powder carrot cake bonbon.',
            'cake jujubes fruitcake. Marzipan danish tart chocolate icing. Chocolate bar sugar plum gummies',
            'plum donut bear claw gummies cheesecake chocolate cake. Bonbon apple pie brownie liquorice icing',
            'roll sesame snaps candy canes lemon drops chocolate . Cotton candy dragée apple pie chupa chups bear',
            'fruitcake halvah. Oat cake jujubes powder chocolate cake cotton candy marzipan brownie. Apple pie',
            'fruitcake marshmallow ice cream cupcake. Jelly macaroon chocolate pudding croissant chocolate bar.',
        ]];

        $this->assertEquals($expected, collect($results['data'] ?? [])->pluck('snippets.content')->all());
    }

    #[Test]
    public function it_can_search_for_plus_signs()
    {
        $comb = new Comb([
            ['content' => '+Content'],
        ]);

        $result = $comb->lookUp('+');
        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        $this->assertSame(1, $result['info']['total_results']);
    }

    #[Test]
    public function it_can_search_for_slashes()
    {
        $comb = new Comb([
            ['content' => 'Cont\ent'],
            ['content' => 'Cont/ent'],
        ]);

        $result = $comb->lookUp('\\');
        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        $this->assertSame(1, $result['info']['total_results']);

        $result = $comb->lookUp('/');
        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        $this->assertSame(1, $result['info']['total_results']);

        $result = $comb->lookUp('Cont\\e');
        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        $this->assertSame(1, $result['info']['total_results']);
    }

    #[Test]
    public function it_can_search_for_umlauts()
    {
        $comb = new Comb([
            ['content' => 'Üppercase umlaut'],
            ['content' => 'Lowercase ümlaut'],
        ]);

        $result = $comb->lookUp('ü');
        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        $this->assertSame(2, $result['info']['total_results']);
    }

    #[Test]
    public function it_filters_out_results_with_disallowed_words()
    {
        $comb = new Comb([
            ['title' => 'Pizza', 'ingredients' => ['Tomato', 'Cheese', 'Bread']],
            ['title' => 'Tomato Soup', 'ingredients' => ['Tomato', 'Water', 'Salt']],
            ['title' => 'Chicken & Sweetcorn Soup', 'ingredients' => ['Chicken', 'Sweetcorn', 'Water']],
        ]);

        $results = $comb->lookUp('soup -tomato');

        $this->assertEquals(['Chicken & Sweetcorn Soup'], collect($results['data'] ?? [])->pluck('data.title')->all());
    }

    public static function searchesProvider()
    {
        return [
            'string with single result' => ['jane', ['Jane Doe']],
            'string with multiple results' => ['doe', ['John Doe', 'Jane Doe']],
            'email' => ['john@doe.com', ['John Doe']],
        ];
    }
}
