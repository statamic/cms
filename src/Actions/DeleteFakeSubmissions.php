<?php

namespace Statamic\Actions;

use Exception;
use Statamic\Facades\Form;
use Statamic\Facades\User;

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
        return $user->can('delete', $item);
    }

    public function run($items, $values)
    {
        $formHandle = $this->context['form'] ?? null;
        $form = $formHandle ? Form::find($formHandle) : null;

        if (! $form) {
            throw new Exception(__('statamic::messages.form_fake_submissions_form_not_found'));
        }

        $fakeSubmissions = $form->submissions()->filter(fn ($submission) => (bool) $submission->get('fake'));
        $currentUser = request()->user() ? User::fromUser(request()->user()) : null;
        $requiredPermission = "delete {$form->handle()} form submissions";
        $isSuper = $currentUser && method_exists($currentUser, 'isSuper') && $currentUser->isSuper();
        $canConfigureForms = $currentUser && method_exists($currentUser, 'hasPermission') && $currentUser->hasPermission('configure forms');
        $canDeleteSubmissions = $currentUser && method_exists($currentUser, 'hasPermission') && $currentUser->hasPermission($requiredPermission);

        if (! $isSuper && ! $canConfigureForms && ! $canDeleteSubmissions) {
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
