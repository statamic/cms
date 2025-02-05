@php
    use function Statamic\trans as __;
@endphp

@php
    use Statamic\Support\Arr;
@endphp

<div class="card overflow-hidden p-0">
    <div class="flex items-center justify-between border-b p-4 dark:border-b dark:border-dark-900 dark:bg-dark-650">
        <h2>
            <a class="flex items-center" href="{{ $form->showUrl() }}">
                <div class="h-6 w-6 text-gray-800 dark:text-dark-200 ltr:mr-2 rtl:ml-2">
                    @cp_svg('icons/light/drawer-file')
                </div>
                <span v-pre>{{ $title }}</span>
            </a>
        </h2>
    </div>
    <div>
        @if (! $submissions)
            <p class="p-4 text-sm text-gray-600">{{ __('This form is awaiting responses') }}</p>
        @else
            <table class="data-table">
                @foreach ($submissions as $submission)
                    <tr>
                        @foreach ($fields as $key => $field)
                            <td>
                                <a
                                    href="{{ cp_route('forms.submissions.show', [$form->handle(), $submission['id']]) }}"
                                >
                                    {{ Arr::get($submission, $field) }}
                                </a>
                            </td>
                        @endforeach

                        <td class="ltr:text-right rtl:text-left">
                            {{ $submission['date']->diffInDays() <= 14 ? $submission['date']->diffForHumans() : $submission['date']->format($format) }}
                        </td>
                    </tr>
                @endforeach
            </table>
        @endif
    </div>
</div>
