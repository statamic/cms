<?php

namespace Statamic\Actions;

use Exception;
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
        return $item->isSpam() && $user->can('markAsNotSpam', $item);
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
        $failures = $items->filter(function ($submission) {
            $submission->markAsNotSpam();

            if ($submission->isPartial()) {
                return $submission->finalize() === false;
            }

            $submission->save();

            return false;
        });

        if ($failures->isNotEmpty()) {
            $total = $items->count();

            throw new Exception($failures->count() === $total
                ? trans_choice('statamic::messages.mark_as_not_spam_action_failed', $total)
                : __('statamic::messages.mark_as_not_spam_action_partially_failed', ['success' => $total - $failures->count(), 'total' => $total]));
        }

        return [
            'message' => trans_choice('Submission marked as not spam|Submissions marked as not spam', $items->count()),
        ];
    }
}
