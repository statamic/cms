@extends('layout')

@section('content')

        <div class="flexy mb-24">
            <h1 class="fill">{{ translate('cp.nav_forms') }}</h1>
            @can('super')
                <a href="{{ route('form.create') }}" class="btn btn-primary">{{ t('create_form') }}</a>
            @endcan
        </div>

        @if(count($forms) == 0)
        <div class="card"
            <div class="no-results">
                <span class="icon icon-download"></span>
                <h2>{{ trans_choice('cp.forms', 2) }}</h2>
                <h3>{{ trans('cp.forms_empty') }}</h3>
                @can('super')
                    <a href="{{ route('form.create') }}" class="btn btn-default btn-lg">{{ trans('cp.create_form') }}</a>
                @endcan
            </div>
        </div>
        @endif

    @if(count($forms) > 0)
    <div class="card flush">
        <table class="dossier">
            <tbody>
            @foreach($forms as $form)
                <tr>
                    <td class="cell-title">
                        <div class="stat">
                            <span class="icon icon-documents"></span>
                            {{ $form['count'] }}
                        </div>
                        <a href="{{ $form['show_url'] }}">{{ $form['title'] }}</a>
                    </td>
                </tr>
                </a>
            @endforeach
            </tbody>
        </table>
    </div>
    @endif

@endsection
