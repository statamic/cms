@extends('statamic::layout')
@section('title', Statamic::crumb($form->title(), 'Forms'))
@section('wrapper_class', 'max-w-full')

@section('content')

    <header class="mb-3">
        @include('statamic::partials.breadcrumb', [
            'url' => cp_route('forms.index'),
            'title' => __('Forms')
        ])
        <div class="flex items-center">
            <h1 class="flex-1">
                {{ $form->title() }}
            </h1>

            <dropdown-list class="mr-1">
                @can('edit', $form)
                    <dropdown-item :text="__('Edit Form')" redirect="{{ $form->editUrl() }}"></dropdown-item>
                @endcan
                @can('delete', $form)
                    <dropdown-item :text="__('Delete Form')" class="warning" @click="$refs.deleter.confirm()">
                        <resource-deleter
                            ref="deleter"
                            resource-title="{{ $form->title() }}"
                            route="{{ $form->deleteUrl() }}"
                            redirect="{{ cp_route('forms.index') }}"
                        ></resource-deleter>
                    </dropdown-item>
                @endcan
            </dropdown-list>

            <dropdown-list>
                <button class="btn" slot="trigger">{{ __('Export Submissions') }}</button>
                <dropdown-item :text="__('Export as CSV')" redirect="{{ cp_route('forms.export', ['type' => 'csv', 'form' => $form->handle()]) }}?download=true"></dropdown-item>
                <dropdown-item :text="__('Export as JSON')" redirect="{{ cp_route('forms.export', ['type' => 'json', 'form' => $form->handle()]) }}?download=true"></dropdown-item>
            </dropdown-list>
        </div>
    </header>

    @if (! empty($form->metrics()))
    <div class="metrics mb-3">
        @foreach($form->metrics() as $metric)
            <div class="card px-3">
                <h3 class="mb-2 font-bold text-grey">{{ $metric->label() }}</h3>
                <div class="text-4xl">{{ $metric->result() }}</div>
            </div>
        @endforeach
    </div>
    @endif

    <form-submission-listing
        form="{{ $form->handle() }}"
        action-url="{{ cp_route('forms.submissions.actions.run', $form->handle()) }}"
        initial-sort-column="datestamp"
        initial-sort-direction="desc"
        :initial-columns="{{ $columns->toJson() }}"
        v-cloak
    >
        <div slot="no-results" class="text-center border-2 border-dashed rounded-lg">
            <div class="max-w-md mx-auto px-4 py-8">
                @cp_svg('empty/form')
                <h1 class="my-3">{{ __('No submissions') }}</h1>
            </div>
        </div>
    </form-submission-listing>

@endsection
