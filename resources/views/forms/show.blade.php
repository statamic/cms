@php
    use function Statamic\trans as __;
@endphp

@extends('statamic::layout')
@section('title', Statamic::crumb($form->title(), 'Forms'))

@section('content')
    <ui-header title="{{ __($form->title()) }}" icon="forms">
        @if (\Statamic\Facades\User::current()->can('edit', $form) || \Statamic\Facades\User::current()->can('delete', $form))
            <ui-dropdown placement="left-start" class="me-2">
                <ui-dropdown-menu>
                    @can('edit', $form)
                        <ui-dropdown-item :text="__('Configure Form')" icon="cog" href="{{ $form->editUrl() }}"></ui-dropdown-item>
                    @endcan

                    @can('configure form fields')
                        <ui-dropdown-item
                            :text="__('Edit Blueprint')"
                            icon="blueprint-edit"
                            href="{{ cp_route('blueprints.forms.edit', $form->handle()) }}"
                        ></ui-dropdown-item>
                    @endcan

                    @can('delete', $form)
                        <ui-dropdown-item :text="__('Delete Form')" icon="trash" variant="destructive" @click="$refs.deleter.confirm()"></ui-dropdown-item>
                    @endcan
                </ui-dropdown-menu>
            </ui-dropdown>

            <ui-command-palette-item
                category="{{ Statamic\CommandPalette\Category::Actions }}"
                :text="__('Configure Form')"
                icon="cog"
                url="{{ $form->editUrl() }}"
            >
            </ui-command-palette-item>

            <ui-command-palette-item
                category="{{ Statamic\CommandPalette\Category::Actions }}"
                :text="__('Edit Blueprint')"
                icon="blueprint-edit"
                url="{{ cp_route('blueprints.forms.edit', $form->handle()) }}"
            >
            </ui-command-palette-item>

            <ui-command-palette-item
                category="{{ Statamic\CommandPalette\Category::Actions }}"
                :text="__('Delete Form')"
                icon="trash"
                :action="() => $refs.deleter.confirm()"
            >
            </ui-command-palette-item>

            @can('delete', $form)
                <resource-deleter
                    ref="deleter"
                    resource-title="{{ $form->title() }}"
                    route="{{ $form->deleteUrl() }}"
                    redirect="{{ cp_route('forms.index') }}"
                ></resource-deleter>
            @endcan
        @endif

        @if (($exporters = $form->exporters()) && $exporters->isNotEmpty())
            <ui-dropdown>
                <template #trigger>
                    <ui-button :text="__('Export Submissions')"></ui-button>
                </template>
                <ui-dropdown-menu>
                    @foreach ($exporters as $exporter)
                        <ui-dropdown-item
                            text="{{ $exporter->title() }}"
                            href="{{ $exporter->downloadUrl() }}"
                        ></ui-dropdown-item>
                    @endforeach
                </ui-dropdown-menu>
            </ui-dropdown>
            @foreach ($exporters as $exporter)
                <ui-command-palette-item
                    category="{{ Statamic\CommandPalette\Category::Actions }}"
                    :text="[ __('Export Submissions'), '{{ $exporter->title() }}' ]"
                    icon="save"
                    url="{{ $exporter->downloadUrl() }}"
                    prioritize
                >
                </ui-command-palette-item>
            @endforeach
        @endif
    </ui-header>

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
        sort-column="datestamp"
        sort-direction="desc"
        :columns="{{ $columns->toJson() }}"
        :filters="{{ $filters->toJson() }}"
        v-cloak
    ></form-submission-listing>
@endsection
