@php
    use function Statamic\trans as __;
@endphp

@extends('statamic::layout')
@section('title', __('Edit Blueprint'))

@section('content')
    <blueprint-builder
        show-title
        action="{{ cp_route('blueprints.additional.update', [$blueprint->namespace(), $blueprint->handle()]) }}"
        :initial-blueprint="{{ json_encode($blueprintVueObject) }}"
    >
        @if ($blueprint->isResettable())
            <template #actions>
                <ui-dropdown>
                    <ui-dropdown-menu>
                        <ui-dropdown-item
                            :text="__('Reset')"
                            variant="destructive"
                            @click="$refs.resetter.confirm()"
                        ></ui-dropdown-item>
                    </ui-dropdown-menu>
                </ui-dropdown>
                <blueprint-resetter
                    ref="resetter"
                    route="{{ $blueprint->resetAdditionalBlueprintUrl() }}"
                    :resource="{{ Js::from($blueprint) }}"
                    reload
                ></blueprint-resetter>
            </template>
        @endif
    </blueprint-builder>

    <x-statamic::docs-callout
        :topic="__('Blueprints')"
        :url="Statamic::docsUrl('blueprints')"
    />
@endsection
