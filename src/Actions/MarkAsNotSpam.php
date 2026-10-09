<?php

namespace Statamic\Actions;

use Statamic\Contracts\Forms\Submission;

use function Statamic\trans as __;
use function Statamic\trans_choice;

class MarkAsNotSpam extends Action
{
    protected $icon = 'checkmark-circle';

    public static function title()
    {
        return __('Mark as Not Spam');
    }

    public function visibleTo($item)
    {
        return $item instanceof Submission && $item->isSpam();
    }

    public function authorize($user, $item)
    {
        return $user->can('markAsNotSpam', $item);
    }

    public function buttonText()
    {
        /** @translation */
        return 'Mark as Not Spam|Mark :count Submissions as Not Spam';
    }

    public function confirmationText()
    {
        /** @translation */
        return 'statamic::messages.mark_as_not_spam_action_confirmation';
    }

    public function run($items, $values)
    {
        $items->each(function ($submission) {
            $submission->markAsNotSpam();

            $submission->isPartial() ? $submission->finalize() : $submission->save();
        });

        return [
            'message' => trans_choice('Submission marked as not spam|Submissions marked as not spam', $items->count()),
        ];
    }
}
