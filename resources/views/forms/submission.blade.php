@extends('statamic::layout')
@section('title', Statamic::crumb('Submission ' . $submission->id(), $submission->form->title(), 'Forms'))

@section('content')

    <header class="mb-3">
        @include('statamic::partials.breadcrumb', [
            'url' => cp_route('forms.show', $submission->form->handle()),
            'title' =>  $submission->form->title()
        ])
        <h1>{{ $submission->date()->format('M j, Y @ H:i') }}</h1>
    </header>

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
                            {!! strip_tags(collect($submission->get($name))->implode(', '), '<a>') !!}
                        @endif
                    </td>
                </tr>
            @endforeach
        </table>

    </div>

@endsection
