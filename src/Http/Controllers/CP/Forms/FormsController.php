<?php

namespace Statamic\Http\Controllers\CP\Forms;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Statamic\Contracts\Forms\Form as FormContract;
use Statamic\CP\Column;
use Statamic\Facades\Form;
use Statamic\Facades\User;
use Statamic\Forms\ConfigFields;
use Statamic\Http\Controllers\CP\CpController;
use Statamic\Http\Controllers\CP\Forms\Concerns\ProvidesFormAbilities;
use Statamic\Rules\Handle;
use Statamic\Statamic;
use Statamic\Support\Str;

use function Statamic\trans as __;

class FormsController extends CpController
{
    use ProvidesFormAbilities;

    public function index(Request $request)
    {
        $this->authorize('index', FormContract::class);

        $user = User::current();

        $columns = [Column::make('title')->label(__('Title'))];

        $forms = Form::all()->filter(fn ($form) => $user->can('view', $form));

        if ($forms->contains(fn ($form) => $user->can('viewSubmissions', $form))) {
            $columns[] = Column::make('submissions')->label(__('Submissions'));
        }

        $forms = $forms
            ->map(function ($form) use ($user) {
                $canViewSubmissions = $user->can('viewSubmissions', $form);

                return [
                    'id' => $form->handle(),
                    'title' => __($form->title()),
                    'status' => $form->status(),
                    'submissions' => $canViewSubmissions ? $form->querySubmissions()->whereNull('partial')->count() : null,
                    'show_url' => $form->showUrl(),
                    'submissions_url' => $form->submissionsUrl(),
                    'edit_url' => $form->editUrl(),
                    'can_edit' => $user->can('edit', $form),
                    'can_view_submissions' => $canViewSubmissions,
                ];
            })
            ->values();

        return Inertia::render('forms/Index', [
            'forms' => $forms,
            'initialColumns' => $columns,
            'actionUrl' => cp_route('forms.actions.run'),
            'canCreate' => $user->can('create', FormContract::class) && $this->canCreateAdditionalForms(),
            'createUrl' => cp_route('forms.create'),
            'configureEmailUrl' => cp_route('utilities.email'),
        ]);
    }

    public function show($form)
    {
        $this->authorize('view', $form);

        $user = User::current();

        if (
            $user->can('editFields', $form)
            && ($user->cant('viewSubmissions', $form) || $form->querySubmissions()->count() === 0)
        ) {
            return redirect()->route('statamic.cp.forms.builder.edit', $form->handle());
        }

        return redirect()->route('statamic.cp.forms.submissions.index', $form->handle());
    }

    public function create()
    {
        $this->authorizeProIf(! $this->canCreateAdditionalForms());

        $this->authorize('create', FormContract::class);

        return Inertia::render('forms/Create', [
            'submitUrl' => cp_route('forms.store'),
        ]);
    }

    public function store(Request $request)
    {
        $this->authorizeProIf(! $this->canCreateAdditionalForms());

        $this->authorize('create', FormContract::class, __('You are not authorized to create forms.'));

        $request->validate([
            'title' => 'required',
            'handle' => ['nullable', new Handle],
        ]);

        $handle = $request->handle ?? Str::snake($request->title);

        if (Form::find($handle)) {
            throw new \Exception(__('Form already exists'));
        }

        $form = tap(Form::make($handle)->title($request->title))->save();

        session()->flash('success', __('Form created'));

        return ['redirect' => $form->showUrl()];
    }

    public function edit($form)
    {
        $this->authorize('edit', $form);

        $blueprint = ConfigFields::blueprint($form);

        $values = array_merge($form->data()->all(), [
            'handle' => $form->handle(),
            'title' => __($form->title()),
            'honeypot' => $form->honeypot(),
            'store' => $form->store(),
            'email' => $form->email(),
            'generate_fake_submissions' => (bool) $form->get('generate_fake_submissions', true),
        ]);

        $fields = $blueprint
            ->fields()
            ->addValues($values)
            ->preProcess();

        return Inertia::render('forms/Edit', [
            'form' => $form,
            'blueprint' => $blueprint->toPublishArray(),
            'initialValues' => $fields->values(),
            'initialMeta' => $fields->meta(),
            'action' => cp_route('forms.update', $form->handle()),
            'can' => $this->formAbilities($form),
        ]);
    }

    public function update($form, Request $request)
    {
        $this->authorize('edit', $form);

        $fields = ConfigFields::blueprint($form)->fields()->addValues($request->all());

        $fields->validate();

        $values = $fields->process()->values()->all();

        $data = collect($values)->except(['title', 'honeypot', 'store', 'email']);

        $form
            ->title($values['title'])
            ->honeypot($values['honeypot'])
            ->store($values['store'])
            ->email($values['email'])
            ->merge($data);

        $form->save();

        $this->success(__('Saved'));
    }

    public function destroy($form)
    {
        $this->authorize('delete', $form, 'You are not authorized to delete this form.');

        $form->delete();
    }

    private function canCreateAdditionalForms(): bool
    {
        return Form::all()->isEmpty()
            || Statamic::pro()
            || Statamic::formsProInstalled();
    }
}
