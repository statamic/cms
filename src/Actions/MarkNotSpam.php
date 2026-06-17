<?php

namespace Statamic\Actions;

use Statamic\Contracts\Forms\Submission;

use function Statamic\trans as __;
use function Statamic\trans_choice;

class MarkNotSpam extends Action
{
    protected $icon = 'checkmark-circle';

    public static function title()
    {
        return __('Mark As Not Spam');
    }

    public function visibleTo($item)
    {
        return $item instanceof Submission && $item->isSpam();
    }

    public function authorize($user, $submission)
    {
        return $user->can('delete', $submission);
    }

    public function confirmationText()
    {
        /** @translation */
        return 'Are you sure you want to mark this submission as not spam?|Are you sure you want to mark these :count submissions as not spam?';
    }

    public function buttonText()
    {
        /** @translation */
        return 'Mark As Not Spam|Mark :count as Not Spam';
    }

    public function run($submissions, $values)
    {
        $submissions->each->complete();

        return trans_choice('Submission marked as not spam|Submissions marked as not spam', $submissions->count());
    }
}
