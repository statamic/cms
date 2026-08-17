<?php

namespace Statamic\Actions;

use Statamic\Contracts\Forms\Submission;

use function Statamic\trans as __;
use function Statamic\trans_choice;

class MarkAsSpam extends Action
{
    protected $confirm = false;

    protected $icon = 'alert-warning-exclamation-mark';

    public static function title()
    {
        return __('Mark as Spam');
    }

    public function visibleTo($item)
    {
        return $item instanceof Submission && ! $item->isSpam();
    }

    public function authorize($user, $item)
    {
        return $user->can('markAsSpam', $item);
    }

    public function buttonText()
    {
        /** @translation */
        return 'Mark as Spam|Mark :count Submissions as Spam';
    }

    public function run($items, $values)
    {
        $items->each(fn ($submission) => $submission->markAsSpam()->save());

        return [
            'message' => trans_choice('Submission marked as spam|Submissions marked as spam', $items->count()),
        ];
    }
}
