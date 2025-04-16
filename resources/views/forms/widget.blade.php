<form-widget
    form="{{ $form->handle() }}"
    title="{{ $title }}"
    :fields='@json($fields)'
    :initial-per-page="{{ $limit }}"
></form-widget>
