<li class="{{ current_class($item->currentClass()) }}">
    <a href="{{ $item->url() }}">
        <i>@svg($item->icon())</i><span>{{ __($item->name()) }}</span>
        <updates-badge class="ml-1" :initial-count="{{ Facades\Statamic\Updater\UpdatesCount::get() }}"></updates-badge>
    </a>
</li>
