@extends('statamic::layout')

@section('content')

    <form-submission-listing inline-template v-cloak
         get="{{ route('form.submissions', $form->name()) }}">

        <div class="form-submission-listing">

            <div class="flexy mb-3">
                <h1 class="fill">{{ $form->title() }}</h1>

                @can('super')
                <a href="{{ route('form.edit', ['form' => $form->name()]) }}" class="btn mr-1">{{ t('configure') }}</a>
                @endcan

                <div class="btn-group">
                    <a href="{{ route('form.export', ['type' => 'csv', 'form' => $form->name()]) }}?download=true"
                       class="btn">{{ t('export') }}</a>
                    <button type="button" class="btn dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <span class="caret"></span>
                        <span class="sr-only">{{ translate('cp.toggle_dropdown') }}</span>
                    </button>
                    <ul class="dropdown-menu">
                        <li><a href="{{ route('form.export', ['type' => 'csv', 'form' => $form->name()]) }}?download=true">{{ t('export_csv') }}</a></li>
                        <li><a href="{{ route('form.export', ['type' => 'json', 'form' => $form->name()]) }}?download=true">{{ t('export_json') }}</a></li>
                    </ul>
                </div>
            </div>

            @if (! empty($form->metrics()))
            <div class="metrics mb-3">
                @foreach($form->metrics() as $metric)
                    <div class="card metric m-0 simple">
                        <div class="count">
                            <small>{{ $metric->label() }}</small>
                            <h2>{{ $metric->result() }}</h2>
                        </div>
                    </div>
                @endforeach
            </div>
            @endif

            <div class="card" v-if="noItems">
                <div class="no-results">
                    <span class="icon icon-documents"></span>
                    <h2>{{ $form->title() }}</h2>
                    <h3>{{ trans('cp.empty_responses') }}</h3>
                </div>
            </div>

            <div class="card flush dossier-for-mobile" v-else>
                <div class="loading" v-if="loading">
                    <span class="icon icon-circular-graph animation-spin"></span> {{ translate('cp.loading') }}
                </div>

                <dossier-table v-if="hasItems" :items="items" :options="tableOptions"></dossier-table>
            </div>
        </div>
    </form-submission-listing>

@endsection
