@extends('statamic::layout')

@section('content')

    <global-publish-form
        action="{{ cp_route('globals.update', $set->id()) }}"
        :initial-fieldset="{{ json_encode($blueprint->toPublishArray()) }}"
        :initial-values="{{ json_encode($values) }}"
        :initial-meta="{{ json_encode($meta) }}"
        inline-template
    >
        <div>
            <div class="flex mb-3 items-center">
                <h1 class="flex-1">
                    <small class="subhead block">
                        <a href="{{ cp_route('globals.index') }}">{{ __('Globals') }}</a>
                    </small>
                    {{ $set->get('title') }}
                </h1>

                @can('create', 'Statamic\Contracts\Data\Globals\GlobalSet')
                    <configure-set
                        save-url="{{ cp_route('globals.update-meta', $set->id()) }}"
                        id="{{ $set->id() }}"
                        initial-title="{{ $set->title() }}"
                        initial-handle="{{ $set->handle() }}"
                        initial-blueprint="{{ $set->blueprint()->handle() }}"
                    ></configure-set>
                @endcan

                @if (! $blueprint->isEmpty())
                    <a href="" class="btn btn-primary ml-2" @click.prevent="save">{{ __('Save') }}</a>
                @endif
            </div>

            @if ($blueprint->isEmpty())
                <div class="text-center mt-5 border-2 border-dashed rounded-lg px-4 py-8">
                    <div class="max-w-md mx-auto opacity-50">
                        @svg('empty/global')
                        <h1 class="my-3">This Global Set has no fields.</h1>
                        <p>You can add fields to the Blueprint, or you can manually add variables to the set itself.</p>
                    </div>
                </div>
            @else
                <publish-container
                    name="base"
                    :fieldset="fieldset"
                    :values="initialValues"
                    :meta="initialMeta"
                    :errors="errors"
                    @updated="values = $event"
                >
                    <div slot-scope="{ }">
                        <div class="alert alert-danger mb-2" v-if="error" v-text="error" v-cloak></div>
                        <publish-sections></publish-sections>
                    </div>
                </publish-container>
            @endif
        </div>
    </global-publish-form>

@endsection
