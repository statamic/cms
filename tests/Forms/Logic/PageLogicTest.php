<?php

namespace Tests\Forms\Logic;

use Facades\Statamic\Console\Processes\Composer;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Form;
use Statamic\Forms\Logic\PageLogic;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class PageLogicTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    public function setUp(): void
    {
        parent::setUp();

        Composer::shouldReceive('isInstalled')->andReturnFalse()->byDefault();
        Composer::shouldReceive('isInstalled')->with('statamic/forms-pro')->andReturnTrue()->byDefault();
    }

    private function form(array $pages)
    {
        return Form::make('survey')->formFields(['pages' => $pages]);
    }

    private function page(string $id, array $rules = [])
    {
        return [
            'id' => $id,
            'rules' => $rules,
            'sections' => [['fields' => [['handle' => $id.'_field', 'field' => ['type' => 'short_answer']]]]],
        ];
    }

    private function rule(string $destination, array $conditions)
    {
        return ['conditions' => $conditions, 'destination' => $destination];
    }

    private function condition(string $field, string $operator, mixed $value)
    {
        return ['field' => $field, 'operator' => $operator, 'value' => $value];
    }

    #[Test]
    public function it_builds_the_full_path_through_the_form()
    {
        $form = $this->form([
            $this->page('one', [$this->rule('three', [$this->condition('colour', 'equals', 'blue')])]),
            $this->page('two'),
            $this->page('three'),
        ]);

        // No rule matches, so the path runs through every page sequentially.
        $this->assertEquals(['one', 'two', 'three'], (new PageLogic($form))->path(['colour' => 'red']));

        // The matching rule skips page two.
        $this->assertEquals(['one', 'three'], (new PageLogic($form))->path(['colour' => 'blue']));
    }

    #[Test]
    public function it_builds_the_path_up_to_a_given_page()
    {
        $form = $this->form([
            $this->page('one'),
            $this->page('two'),
            $this->page('three'),
        ]);

        $this->assertEquals(['one', 'two'], (new PageLogic($form))->pathTo([], 'two'));
    }

    #[Test]
    public function it_advances_to_the_next_sequential_page_when_no_rules_match()
    {
        $form = $this->form([
            $this->page('one', [$this->rule('three', [$this->condition('colour', 'equals', 'blue')])]),
            $this->page('two'),
            $this->page('three'),
        ]);

        $this->assertEquals('two', (new PageLogic($form))->nextPage('one', ['colour' => 'red']));
    }

    #[Test]
    public function it_routes_to_a_matching_rules_destination()
    {
        $form = $this->form([
            $this->page('one', [$this->rule('three', [$this->condition('colour', 'equals', 'blue')])]),
            $this->page('two'),
            $this->page('three'),
        ]);

        $this->assertEquals('three', (new PageLogic($form))->nextPage('one', ['colour' => 'blue']));
    }

    #[Test]
    public function the_first_matching_rule_wins()
    {
        $form = $this->form([
            $this->page('one', [
                $this->rule('two', [$this->condition('colour', 'equals', 'blue')]),
                $this->rule('three', [$this->condition('colour', 'equals', 'blue')]),
            ]),
            $this->page('two'),
            $this->page('three'),
        ]);

        $this->assertEquals('two', (new PageLogic($form))->nextPage('one', ['colour' => 'blue']));
    }

    #[Test]
    public function the_final_page_has_no_next_page()
    {
        $form = $this->form([
            $this->page('one'),
            $this->page('two'),
        ]);

        $this->assertNull((new PageLogic($form))->nextPage('two', []));
        $this->assertTrue((new PageLogic($form))->isFinalPage('two', []));
        $this->assertFalse((new PageLogic($form))->isFinalPage('one', []));
    }

    #[Test]
    public function a_rule_pointing_at_a_deleted_page_is_skipped()
    {
        $form = $this->form([
            $this->page('one', [$this->rule('gone', [$this->condition('colour', 'equals', 'blue')])]),
            $this->page('two'),
        ]);

        $this->assertEquals('two', (new PageLogic($form))->nextPage('one', ['colour' => 'blue']));
    }

    #[Test]
    public function an_unknown_current_page_has_no_next_page()
    {
        $form = $this->form([
            $this->page('one'),
            $this->page('two'),
        ]);

        $this->assertNull((new PageLogic($form))->nextPage('nonexistent', []));
    }
}
