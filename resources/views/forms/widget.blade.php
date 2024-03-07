@php use function Statamic\trans as __; @endphp

<div class="card p-0 overflow-hidden">
    <div class="flex justify-between items-center p-4">
        <h2>
            <a class="flex items-center" href="{{ $form->showUrl() }}">
                <div class="h-6 w-6 mr-2 text-gray-800">
                    @cp_svg('icons/light/drawer-file')
                </div>
                <span v-pre>{{ $title }}</span>
            </a>
        </h2>
    </div>
    <div>
        @if ( ! $submissions)
            <p class="p-4 pt-2 text-sm text-gray-600">{{ __('This form is awaiting responses') }}</p>
        @else
            <table class="data-table">
                @foreach($submissions as $submission)
                    <tr>
                        @foreach($fields as $key => $field)
                        <td><a href="{{ cp_route('forms.submissions.show', [$form->handle(), $submission['id']]) }}">{{ array_get($submission, $field) }}</a></td>
                        @endforeach
                        <td class="text-right">
                            {{ ($submission['date']->diffInDays() <= 14) ? $submission['date']->diffForHumans() : $submission['date']->format($format) }}
                        </td>
                    </tr>
                @endforeach
            </table>
        @endif
    </div>
</div>
