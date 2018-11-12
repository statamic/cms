@extends('statamic::layout')

@section('content')

    <div class="flex mb-3">
        <h1>
            <a href="{{ cp_route('forms.index')}}">{{ __('Forms') }}</a>
            @svg('chevron-right')
            <a href="{{ cp_route('forms.show', $submission->form->handle()) }}">
                {{ $submission->form->title() }}
            </a>
            @svg('chevron-right')
            {{ __('Submission') }}
        </h1>
    </div>

    <div class="card" v-pre>
        <table class="dossier mt-0">
            <tr>
                <th width="25%">{{ t('date') }}</th>
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
