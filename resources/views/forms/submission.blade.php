@extends('statamic::layout')
@section('title', Statamic::crumb('Submission ' . $submission->id(), $submission->form->title(), 'Forms'))

@section('content')

    <div class="flex mb-3">
        <h1>
            <small class="subhead block">
                <a href="{{ cp_route('forms.index')}}">{{ __('Forms') }}</a>
            </small>
            <a href="{{ cp_route('forms.show', $submission->form->handle()) }}">
                {{ $submission->form->title() }}
            </a>
            @svg('chevron-right')
            {{ $submission->date()->format('M j, Y @ h:m') }}
        </h1>
    </div>

    <div class="card" v-pre>
        <table class="data-table mt-0">
            <tr class="border-none">
                <th width="25%">{{ __('Date') }}</th>
                <td>{{ $submission->formattedDate() }}</td>
            </tr>
            @foreach($submission->fields() as $name => $field)
                <tr>
                    <th>{{ array_get($field, 'display', $name) }}</th>
                    <td>
                        @if(! is_array($submission->get($name)))
                            {!! strip_tags($submission->get($name), '<a>') !!}
                        @else
                            <table>
                                @foreach($submission->get($name) as $key => $value)
                                    <tr>
                                        <th>{{ $key }}</th>
                                        <td>
                                            @if(is_array($value))
                                                {{ json_encode($value) }}
                                            @else
                                                {{ $value }}
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </table>
                        @endif
                    </td>
                </tr>
            @endforeach
        </table>

    </div>

@endsection
