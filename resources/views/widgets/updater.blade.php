<updater-widget
    :count="{{ $count }}"
    :has-statamic-update="{{ json_encode($hasStatamicUpdate) }}"
    :updatable-addons="{{ json_encode($updatableAddons) }}"
    :initial-per-page="{{ $limit }}"
></updater-widget>
