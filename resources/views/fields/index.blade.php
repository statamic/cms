@php use function Statamic\trans as __; @endphp

@extends('statamic::layout')
@section('title', __('Fields'))

@section('content')

        <div class="content">
            <h1>{{ __('Fields') }}</h1>
        </div>

        <div class="flex flex-wrap md:-mx-6 mt-6">
            <div class="w-full md:w-1/2 md:px-6 mb-6">
                <div class="card p-0 content border-t-6 border-blue">
                    <div class="p-6">
                        <h2><a href="{{ cp_route('blueprints.index') }}" class="text-gray-900 hover:text-blue">{{ __('Blueprints') }}</a></h2>
                        <p>{{ __('statamic::messages.fields_blueprints_description') }}</p>
                        <p><a href="{{ Statamic::docsUrl('blueprints') }}" class="font-bold text-blue">{{ __('Read the Docs') }}</a><span class="inline-block text-blue w-4 h-4 rtl:mr-2 ltr:ml-2">@cp_svg('icons/light/external-link')</span></p>
                    </div>
                    <div class="flex p-6 border-t items-center">
                        <a href="{{ cp_route('blueprints.create') }}" class="btn-primary rtl:ml-4 ltr:mr-4">{{ __('Create Blueprint') }}</a>
                        @unless($blueprints->isEmpty())
                            <a href="{{ cp_route('blueprints.index') }}" class="font-bold text-blue text-sm hover:text-gray-900">
                                {{ __('View All') }} <span class="font-normal">({{ $blueprints->count() }})</span> @rarr
                            </a>
                        @endunless
                    </div>
                </div>
            </div>
            <div class="w-full md:w-1/2 md:px-6 mb-6">
                <div class="card p-0 content border-t-6 border-blue">
                    <div class="p-6">
                        <h2>{{ __('Fieldsets') }}</h2>
                        <p>{{ __('statamic::messages.fields_fieldsets_description') }}</p>
                        <p><a href="{{ Statamic::docsUrl('fieldsets') }}" class="font-bold text-blue">{{ __('Read the Docs') }}</a><span class="inline-block text-blue w-4 h-4 rtl:mr-2 ltr:ml-2">@cp_svg('icons/light/external-link')</span></p>
                    </div>
                    <div class="flex p-6 border-t items-center">
                        <a href="{{ cp_route('fieldsets.create') }}" class="btn-primary rtl:ml-4 ltr:mr-4">{{ __('Create Fieldset') }}</a>
                        @unless($fieldsets->isEmpty())
                            <a href="{{ cp_route('fieldsets.index') }}" class="font-bold text-blue text-sm hover:text-gray-900">
                                {{ __('View All') }} <span class="font-normal">({{ $fieldsets->count() }})</span> @rarr
                            </a>
                        @endunless
                    </div>
                </div>
            </div>
        </div>

@endsection
