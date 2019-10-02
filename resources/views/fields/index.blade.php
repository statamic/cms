@extends('statamic::layout')
@section('title', __('Blueprints'))

@section('content')

        <div class="content">
            <h1>Fields</h1>
            {{-- <p class="text-base max-w-lg">Each field is an instance of a <b>Field type</b>, which determines its user interface, the data it can store, and how you interact with it in your templates. Fields are organized into Blueprints and reususable Fieldsets.</p> --}}
        </div>

        <div class="flex flex-wrap md:-mx-3 mt-3">
            <div class="w-full md:w-1/2 md:px-3 mb-3">
                <div class="card p-0 content border-t-6 border-blue">
                    <div class="p-3">
                        <h2><a href="{{ cp_route('blueprints.index') }}" class="text-grey-90 hover:text-blue">Blueprints</a></h2>
                        <p>Blueprints let you mix and match fields and fieldsets to create the content structures for collections and other data types.</p>
                        <p><a href="" class="font-bold text-blue">Read the docs</a><span class="inline-block text-blue w-4 h-4 ml-1">@svg('external-link')</span></p>
                    </div>
                    <div class="flex p-3 border-t items-center">
                        <a href="{{ cp_route('blueprints.create') }}" class="btn-primary mr-2">Create Blueprint</a>
                        @unless($blueprints->isEmpty())
                            <a href="{{ cp_route('blueprints.index') }}" class="font-bold text-blue text-sm hover:text-grey-90">
                                View All <span class="font-normal">({{ $blueprints->count() }})</span> &rarr;
                            </a>
                        @endunless
                    </div>
                </div>
            </div>
            <div class="w-full md:w-1/2 md:px-3 mb-3">
                <div class="card p-0 content border-t-6 border-blue">
                    <div class="p-3">
                        <h2>Fieldsets</h2>
                        <p>Fieldsets are simple, flexible, and completely optional groupings of fields that help to organize reusable, pre-configured fields.</p>
                        <p><a href="" class="font-bold text-blue">Read the docs</a><span class="inline-block text-blue w-4 h-4 ml-1">@svg('external-link')</span></p>
                    </div>
                    <div class="flex p-3 border-t items-center">
                        <a href="{{ cp_route('fieldsets.create') }}" class="btn-primary mr-2">Create Fieldset</a>
                        @unless($fieldsets->isEmpty())
                            <a href="{{ cp_route('fieldsets.index') }}" class="font-bold text-blue text-sm hover:text-grey-90">
                                View All <span class="font-normal">({{ $fieldsets->count() }})</span> &rarr;
                            </a>
                        @endunless
                    </div>
                </div>
            </div>
        </div>

@endsection
