<form-widget
    form="{{ $form->handle() }}"
    title="{{ $title }}"
    :fields='@json($fields)'
    :initial-per-page="{{ $limit }}"
    submissions-url="{{ cp_route('forms.show', $form->handle()) }}"
>
    <template #actions>
        <ui-button href="{{ cp_route('forms.show', $form->handle()) }}" size="sm">
            {{ __('View All') }}
        </ui-button>
    </template>
</form-widget>
