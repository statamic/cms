<div class="card p-0 overflow-hidden">
    <div class="flex justify-between items-center p-2">
        <h2><a href="{{ $form->showUrl() }}">{{ $title }}</a></h2>
    </div>
    <div>
        @if ( ! $submissions)
            <p class="text-center my-2">{{ __('This form is awaiting responses') }}</p>
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
