@extends('statamic::layout')
@section('title', __('Blueprints'))

@section('content')

        <div class="card p-5 content">
            <div class="flex">
                <div class="max-w-lg pr-4">
                    <h1 class="mb-2 text-3xl">Fields</h1>
                    <p class="text-base text-grey-80 mb-3">Fields are used to structure your content. This helps you to maintain your content, control output, reuse content, and create relationships <em>between</em> content.</p>
                    <p class="text-base text-grey-80 mb-3">Each field uses a <b>Field type</b>, which determines what the field's input interface will look like, what type of data it stores, and how you can interact with its data in your templates.</p>
                     <div class="flex items-center mt-2">
                        <p><a href="" class="font-bold text-blue">Learn more about fields in the docs</a><span class="inline-block text-blue w-4 h-4 ml-1">@svg('external-link')</span></p>
                    </div>
                </div>
                <div class="h-80 w-80 p-2 text-center">
                    @svg('marketing/fields')
                </div>
            </div>
        </div>

        <div class="content mt-6">
            <h1 class="mb-0">Getting started</h1>
            <p class="text-base">Here's how to get started building and grouping your fields.</p>
        </div>

        <div class="flex flex-wrap -mx-3 mt-3">
            <div class="w-1/2 px-3">
                <div class="card p-3 content border-t-6 border-blue">
                    <h2>Blueprints</h2>
                    <p>Blueprints let you mix and match fields and fieldsets to create the content structures for collections and other data types.</p>
                    <div class="flex items-center mt-2">
                        <a href="{{ cp_route('blueprints.create') }}" class="btn-primary mr-2">Create Blueprint</a>
                        <p><a href="" class="font-bold text-blue">Read the docs</a><span class="inline-block text-blue w-4 h-4 ml-1">@svg('external-link')</span></p>
                    </div>
                </div>
            </div>
            <div class="w-1/2 px-3">
                <div class="card p-3 content border-t-6 border-blue">
                    <h2>Fieldsets</h2>
                    <p>Fieldsets are simple, flexible, and completely optional groupings of fields that help to organize reusable, pre-configured fields.</p>
                    <div class="flex items-center mt-2">
                        <a href="{{ cp_route('fieldsets.create') }}" class="btn-primary mr-2">Create Fieldset</a>
                        <p><a href="" class="font-bold text-blue">Read the docs</a><span class="inline-block text-blue w-4 h-4 ml-1">@svg('external-link')</span></p>
                    </div>
                </div>
            </div>
        </div>

@endsection
