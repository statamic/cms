<form-widget
    form="{{ $form->handle() }}"
    title="{{ $title }}"
    :fields='@json($fields)'
    :initial-per-page="{{ $limit }}"
>
    <template #actions>
        <ui-button href="{{ cp_route('forms.show', $form->handle()) }}">
            {{ __('View All') }}
        </ui-button>
    </template>
</form-widget>
