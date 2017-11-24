<div class="card flush">
    <div class="head">
        <h1><a href="{{ $form->url() }}">{{ $title }}</a></h1>
    </div>
    <div class="card-body pad-16">
        @if ( ! $submissions)
            <p class="text-center light mv-16">{{ trans('cp.empty_responses') }}</p>
        @else
            <table class="dossier">
                @foreach($submissions as $submission)
                    <tr>
                        @foreach($fields as $key => $field)
                        <td><a href="{{ route('form.submission.show', [$form->name(), $submission['id']]) }}">{{ array_get($submission, $field) }}</a></td>
                        @endforeach
                        <td class="minor text-right">
                            {{ ($submission['date']->diffInDays() <= 14) ? $submission['date']->diffForHumans() : $submission['date']->format($format) }}
                        </td>
                    </tr>
                @endforeach
            </table>
        @endif
    </div>
</div>
