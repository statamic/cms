<div class="card flush">
    <div class="head">
        <h1><a href="{{ cp_route('collections.show', $collection->handle()) }}">{{ $title }}</a></h1>
        <a href="{{ $collection->createEntryUrl() }}" class="btn btn-primary">{{ $button }}</a>
    </div>
    <div class="card-body pad-16">
        <table class="dossier">
            @foreach($entries as $entry)
                <tr>
                    <td>
                        <a href="{{ $entry->editUrl() }}">
                            {{ $entry->get('title') }} {{ $entry->published() ? null : '(Draft)' }}
                        </a>
                    </td>

                    {{-- TODO: Entry order type? --}}
                    {{-- @if ($entry->orderType() === 'date') --}}
                    <td class="minor text-right">
                        {{ ($entry->date()->diffInDays() <= 14) ? $entry->date()->diffForHumans() : $entry->date()->format($format) }}
                    </td>
                    {{-- @endif --}}
                </tr>
            @endforeach
        </table>
    </div>
</div>
