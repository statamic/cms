<?php

namespace Tests\Forms;

use Illuminate\Http\Request;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Form;
use Statamic\Facades\User;
use Statamic\Forms\Fieldtype;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class FieldtypeTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    #[Test]
    public function the_index_items_submission_count_excludes_partial_submissions()
    {
        $this->actingAs(tap(User::make()->makeSuper())->save());

        $form = tap(Form::make('test'))->save();
        $form->makeSubmission()->save();
        $form->makeSubmission()->asPartial()->save();

        $items = (new Fieldtype)->getIndexItems(new Request(['paginate' => false]));

        $this->assertEquals(1, $items->firstWhere('id', 'test')['submissions']);
    }
}
