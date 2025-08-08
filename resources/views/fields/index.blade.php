@php
    use function Statamic\trans as __;
@endphp

@extends('statamic::layout')
@section('title', __('Fields'))

@section('content')
    <div class="content">
        <h1>{{ __('Fields') }}</h1>
    </div>

    <div class="mt-6 flex flex-wrap md:-mx-6">
        <div class="mb-6 w-full md:w-1/2 md:px-6">
            <div class="card content border-t-6 border-blue p-0">
                <div class="p-6">
                    <h2>
                        <a href="{{ cp_route('blueprints.index') }}" class="text-gray-900 hover:text-blue">
                            {{ __('Blueprints') }}
                        </a>
                    </h2>
                    <p>{{ __('statamic::messages.fields_blueprints_description') }}</p>
                    <p>
                        <a href="{{ Statamic::docsUrl('blueprints') }}" class="font-bold text-blue">
                            {{ __('Read the Docs') }}
                        </a>
                        <span class="inline-block h-4 w-4 text-blue-600 ltr:ml-2 rtl:mr-2">
                            @cp_svg('icons/light/external-link')
                        </span>
                    </p>
                </div>
                <div class="flex items-center border-t p-6">
                    <a href="{{ cp_route('blueprints.create') }}" class="btn-primary ltr:mr-4 rtl:ml-4">
                        {{ __('Create Blueprint') }}
                    </a>
                    @unless ($blueprints->isEmpty())
                        <a
                            href="{{ cp_route('blueprints.index') }}"
                            class="text-sm font-bold text-blue-600 hover:text-gray-900"
                        >
                            {{ __('View All') }}
                            <span class="font-normal">({{ $blueprints->count() }})</span>
                            @rarr
                        </a>
                    @endunless
                </div>
            </div>
        </div>
        <div class="mb-6 w-full md:w-1/2 md:px-6">
            <div class="card content border-t-6 border-blue p-0">
                <div class="p-6">
                    <h2>{{ __('Fieldsets') }}</h2>
                    <p>{{ __('statamic::messages.fields_fieldsets_description') }}</p>
                    <p>
                        <a href="{{ Statamic::docsUrl('fieldsets') }}" class="font-bold text-blue">
                            {{ __('Read the Docs') }}
                        </a>
                        <span class="inline-block h-4 w-4 text-blue-600 ltr:ml-2 rtl:mr-2">
                            @cp_svg('icons/light/external-link')
                        </span>
                    </p>
                </div>
                <div class="flex items-center border-t p-6">
                    <a href="{{ cp_route('fieldsets.create') }}" class="btn-primary ltr:mr-4 rtl:ml-4">
                        {{ __('Create Fieldset') }}
                    </a>
                    @unless ($fieldsets->isEmpty())
                        <a
                            href="{{ cp_route('fieldsets.index') }}"
                            class="text-sm font-bold text-blue-600 hover:text-gray-900"
                        >
                            {{ __('View All') }}
                            <span class="font-normal">({{ $fieldsets->count() }})</span>
                            @rarr
                        </a>
                    @endunless
                </div>
            </div>
        </div>
    </div>
@endsection
