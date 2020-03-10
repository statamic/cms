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

    <div class="metrics mb-3">
        <div class="flex flex-wrap h-full -mx-2">
            <div class="px-2 w-1/3">
                <div class="shadow bg-white h-full rounded">
                    <header class="p-2">
                        <div class="text-sm font-medium border-b border-dashed pb-1">Total Submissions</div>
                    </header>
                    <div class="flex items-center px-2">
                        <div class="text-3xl leading-none">{{ number_format($form->metrics()->count() + 321, 0) }}</div>
                    </div>
                </div>
            </div>
            <div class="px-2 w-1/3">
                <div class="shadow bg-white h-full rounded">
                    <div class="p-2">
                        <div class="text-sm font-medium border-b border-dashed pb-1 flex items-center justify-between">
                            <span>Last 30 Days</span>
                            <select name="" id="" class="text-xs"><option value="">30 Days</option></select>
                        </div>
                    </div>
                    <sparkline-chart></sparkline-chart>
                </div>
            </div>
            <div class="px-2 w-1/3">
                <div class="shadow bg-white h-full rounded">
                    <header class="p-2">
                        <div class="text-sm font-medium border-b border-dashed pb-1">Spam Stopped</div>
                    </header>
                    <div class="flex items-center px-2">
                        <div class="text-3xl leading-none">4</div>
                    </div>
                </div>
            </div>
        </div>

        @if ($form->metrics()->count() > 0)
        @foreach($form->metrics() as $metric)
            <div class="card px-3">
                <h3 class="mb-2 font-bold text-grey">{{ $metric->label() }}</h3>
                <div class="text-4xl">{{ $metric->result() }}</div>
            </div>
        @endforeach
    @endif
    </div>

    <form-submission-listing
        form="{{ $form->handle() }}"
        action-url="{{ cp_route('forms.submissions.actions', $form->handle()) }}"
        initial-sort-column="datestamp"
        initial-sort-direction="desc"
        v-cloak
    >
        <div slot="no-results" class="text-center border-2 border-dashed rounded-lg">
            <div class="max-w-md mx-auto px-4 py-8">
                @svg('empty/form')
                <h1 class="my-3">{{ __('No submissions') }}</h1>
            </div>
        </div>
    </form-submission-listing>

@endsection
