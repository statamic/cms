@php
    use function Statamic\trans as __;
@endphp

@extends('statamic::layout')
@section('title', Statamic::crumb('Git', __('Utilities')))

@section('content')

<form method="POST" action="{{ cp_route('utilities.git.commit') }}">
    @csrf
    <ui-header title="{{ __('Git') }}" icon="git">
        <ui-button type="submit" variant="primary" {{ $statuses ? '' : 'disabled' }}>
            {{ __('Commit Changes') }}
        </ui-button>
    </ui-header>
</form>

@if ($statuses)
    @foreach ($statuses as $path => $status)
        <ui-card-panel heading="{{ __('Repository') }}" subheading="{{ $path }}">
            <div class="space-y-4">
                <div class="flex flex-wrap gap-2">
                    <ui-badge :prepend="__('Affected files')">
                        {{ $status->totalCount }}
                    </ui-badge>
                    @if ($status->addedCount)
                        <ui-badge :prepend="__('Added')" color="green">
                            {{ $status->addedCount }}
                        </ui-badge>
                    @endif
                    @if ($status->modifiedCount)
                        <ui-badge :prepend="__('Modified')" color="yellow">
                            {{ $status->modifiedCount }}
                        </ui-badge>
                    @endif
                    @if ($status->deletedCount)
                        <ui-badge :prepend="__('Deleted')" color="red">
                            {{ $status->deletedCount }}
                        </ui-badge>
                    @endif
                </div>

                <git-status :status='{{ json_encode($status->status) }}'></git-status>
            </div>
        </ui-card-panel>
    @endforeach
@else
    <ui-card-panel heading="{{ __('Repository') }}">
        <ui-heading>{{ __('statamic::messages.git_nothing_to_commit') }}</ui-heading>
    </ui-card-panel>
@endif

<x-statamic::docs-callout
    topic='{{ __("the Git Integration") }}'
    url="{{ Statamic::docsUrl('git-integration') }}"
/>

@stop
