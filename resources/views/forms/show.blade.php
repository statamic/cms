@php
    use function Statamic\trans as __;
@endphp

@extends('statamic::layout')
@section('title', Statamic::crumb($form->title(), 'Forms'))

@section('content')
    <header class="mb-6">
        <div class="flex items-center">
            <h1 v-pre class="flex-1">
                {{ __($form->title()) }}
            </h1>

            @if (\Statamic\Facades\User::current()->can('edit', $form) || \Statamic\Facades\User::current()->can('delete', $form))
                <dropdown-list class="ltr:mr-2 rtl:ml-2">
                    @can('edit', $form)
                        <dropdown-item :text="__('Edit Form')" redirect="{{ $form->editUrl() }}"></dropdown-item>
                    @endcan

                    @can('configure form fields')
                        <dropdown-item
                            :text="__('Edit Blueprint')"
                            redirect="{{ cp_route('forms.blueprint.edit', $form->handle()) }}"
                        ></dropdown-item>
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
            @endif

            @if (($exporters = $form->exporters()) && $exporters->isNotEmpty())
                <dropdown-list>
                    <template v-slot:trigger>
                        <button class="btn" slot="trigger">{{ __('Export Submissions') }}</button>
                    </template>
                    @foreach ($exporters as $exporter)
                        <dropdown-item
                            text="{{ $exporter->title() }}"
                            redirect="{{ $exporter->downloadUrl() }}"
                        ></dropdown-item>
                    @endforeach
                </dropdown-list>
            @endif
        </div>
    </header>

    @if (! empty($form->metrics()))
        <div class="metrics mb-6">
            @foreach ($form->metrics() as $metric)
                <div class="card px-6">
                    <h3 class="mb-4 font-bold text-gray dark:text-dark-175">{{ $metric->label() }}</h3>
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
        :filters="{{ $filters->toJson() }}"
        v-cloak
    >
        <div slot="no-results" class="rounded-lg border-2 border-dashed text-center dark:border-dark-400">
            <div class="mx-auto max-w-md px-8 py-30">
                @cp_svg('empty/form')
                <h1 class="my-6">{{ __('No submissions') }}</h1>
            </div>
        </div>
    </form-submission-listing>
@endsection
