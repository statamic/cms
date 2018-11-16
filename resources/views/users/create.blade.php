@extends('statamic::layout')

@section('content')

    <user-publish-form
        action="{{ cp_route('users.store') }}"
        method="post"
        :initial-fieldset="{{ json_encode($blueprint->toPublishArray()) }}"
        :initial-values="{{ json_encode($values) }}"
        inline-template
    >
        <div>
            <div class="flex mb-3">
                <h1 class="flex-1">
                    <a href="{{ cp_route('users.index')}}">{{ __('Users') }}</a>
                    @svg('chevron-right')
                    {{ __('Create User') }}
                </h1>
                <a href="" class="btn btn-primary" @click.prevent="save">{{ __('Save') }}</a>
            </div>

            <publish-container
                v-if="fieldset"
                name="base"
                :fieldset="fieldset"
                :values="initialValues"
                :errors="errors"
                @updated="values = $event"
            >
                <div slot-scope="{ }">
                    <div class="alert alert-danger mb-2" v-if="error" v-text="error" v-cloak></div>
                    <publish-sections></publish-sections>
                </div>
            </publish-container>
        </div>
    </user-publish-form>

@endsection





