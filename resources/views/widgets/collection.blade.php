<div class="card p-0 content">
    <div class="p-3 flex items-center">
        <h1 class="flex-1 mb-0"><a href="{{ cp_route('collections.show', $collection->handle()) }}">{{ $title }}</a></h1>
        <a href="{{ $collection->createEntryUrl() }}" class="btn btn-primary">{{ $button }}</a>
    </div>
    <div class="p-3 border-t">
        @foreach ($entries as $entry)
            <p>
                <td>
                    <a href="{{ $entry->editUrl() }}">
                        {{ $entry->get('title') }}
                        @if (! $entry->published())
                            <sup class="text-grey-light">Draft</sup>
                        @endif
                    </a>
                </td>

                @if ($entry->date())
                    <td class="minor text-right">
                        {{ ($entry->date()->diffInDays() <= 14) ? $entry->date()->diffForHumans() : $entry->date()->format($format) }}
                    </td>
                @endif
            </p>
        @endforeach
    </div>
</div>
