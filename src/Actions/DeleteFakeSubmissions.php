<?php

namespace Statamic\Actions;

use Exception;
use Statamic\Facades\Form;
use Statamic\Facades\User;

use function Statamic\trans as __;
use function Statamic\trans_choice;

class DeleteFakeSubmissions extends Action
{
    protected $dangerous = true;

    protected $icon = 'trash';

    public static function title()
    {
        return __('Delete Fake Submissions');
    }

    public function visibleTo($item)
    {
        return false;
    }

    public function authorize($user, $item)
    {
        return $user->can('generateFakeSubmissions', $item->form());
    }

    public function run($items, $values)
    {
        $formHandle = $this->context['form'] ?? null;
        $form = $formHandle ? Form::find($formHandle) : null;

        if (! $form) {
            throw new Exception(__('statamic::messages.form_fake_submissions_form_not_found'));
        }

        $fakeSubmissions = $form->submissions()->filter(fn ($submission) => (bool) $submission->get('_fake'));
        $currentUser = request()->user() ? User::fromUser(request()->user()) : null;

        // The UI always posts the `_all_fake_submissions_` sentinel, which resolves to an
        // empty $items collection in ActionController, so authorize() never actually runs
        // for this flow. This check is the real enforcement point for the UI-driven request.
        if (! $currentUser || $currentUser->cant('generateFakeSubmissions', $form)) {
            throw new Exception(__('You are not authorized to run this action.'));
        }

        if ($fakeSubmissions->isEmpty()) {
            return [
                'message' => __('statamic::messages.form_fake_submissions_none_to_delete'),
            ];
        }

        $failures = $fakeSubmissions->reject(fn ($submission) => $submission->delete());
        $deletedCount = $fakeSubmissions->count() - $failures->count();

        if ($deletedCount === 0) {
            throw new Exception(__('statamic::messages.form_fake_submissions_delete_failed'));
        }

        if ($failures->isNotEmpty()) {
            throw new Exception(__('statamic::messages.form_fake_submissions_partial_delete', [
                'success' => $deletedCount,
                'total' => $fakeSubmissions->count(),
            ]));
        }

        return [
            'message' => trans_choice('statamic::messages.form_fake_submissions_deleted', $deletedCount, [
                'count' => $deletedCount,
            ]),
        ];
    }
}
