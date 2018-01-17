@extends('layout')

@section('content')

    <div class="flexy mb-24">
        <a href="{{ route('form.show', $submission->form->name) }}" class="btn icon round mr-16">&larr;</a>
        <h1>{{ translate_choice('cp.submissions', 1) }}</h1>
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
                            {!! $submission->get($name) !!}
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
