@php
    use function Statamic\trans as __;
@endphp

@extends('statamic::layout')
@section('title', __('Addons'))

@section('content')
    <ui-header title="{{ __('Addons') }}" icon="addons">
        <ui-command-palette-item
            category="{{ Statamic\CommandPalette\Category::Actions }}"
            text="{{ __('Browse the Marketplace') }}"
            icon="external-link"
            url="https://statamic.com/addons"
            open-new-tab
            prioritize
            v-slot="{ text, url, icon }"
        >
            <ui-button variant="primary" :text="text" :href="url" :icon="icon" target="_blank"></ui-button>
        </ui-command-palette-item>
    </ui-header>

    <addon-list :initial-rows="{{ json_encode($addons) }}" :initial-columns="{{ json_encode($columns) }}"></addon-list>

    <x-statamic::docs-callout
        :topic="__('Addons')"
        :url="Statamic::docsUrl('addons')"
    />
@endsection
