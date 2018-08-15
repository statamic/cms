@extends('statamic::layout')

@section('content')
    <div class="flex mb-5">
        <h1>{{ __('The Statamic Playground') }}</h1>
    </div>

    <h2 class="mb-1">Typography</h2>
    <div class="shadow bg-white p-4 rounded-lg overflow-hidden mb-6">
        <h1 class="mb-2">This is first level heading</h1>
        <h2 class="mb-2">This is a second level heading</h2>
        <h3 class="mb-2">This is a third level heading</h3>
        <h4 class="mb-2">This is a fourth level heading</h4>
        <h5 class="mb-2">This is a fifth level heading</h5>
        <h6 class="mb-2">This is a sixth level heading</h6>
        <p>Paragraph text. Lorem ipsum dolor sit amet, consectetur adipisicing elit. Quam error tempore veritatis, laborum, et assumenda? Necessitatibus excepturi enim quidem maxime! Temporibus dolorum fugit aspernatur.
    </div>

    <h2 class="mb-1">Buttons</h2>
    <div class="shadow bg-white p-4 rounded-lg overflow-hidden mb-6">
        <h6 class="mb-2">Flavors</h6>
        <div class="mb-4">
            <button class="btn mr-2">Default Button</button>
            <button class="btn btn-primary mr-2">Primary Button</button>
            <button class="btn btn-secondary mr-2">Secondary Button</button>
        </div>
        <h6 class="mb-2">Disabled States</h6>
        <div class="mb-4">
            <button disabled class="btn disabled mr-2">Default Button</button>
            <button disabled class="btn btn-primary disabled mr-2">Primary Button</button>
            <button disabled class="btn btn-secondary disabled mr-2">Secondary Button</button>
        </div>
        <h6 class="mb-2">Large Variation</h6>
        <div>
            <button class="btn btn-lg mr-2">Default Button</button>
            <button class="btn btn-primary btn-lg mr-2">Primary Button</button>
            <button class="btn btn-secondary btn-lg mr-2">Secondary Button</button>
        </div>
    </div>

    <h2 class="mb-1">Colors</h2>
    <div class="bg-white p-5 shadow rounded-lg overflow-hidden mb-6">
        <h6 class="mb-2">Blues</h6>
        <div class="flex flex-row-reverse text-sm text-center mb-4">
            <div class="text-black bg-blue-light p-3 flex-1">Light</div>
            <div class="text-black bg-blue p-3 flex-1">Base</div>
            <div class="text-white bg-blue-dark p-3 flex-1">Dark</div>
            <div class="text-white bg-blue-darker p-3 flex-1">Darker</div>
        </div>

        <h6 class="mb-2">Greys</h6>
        <div class="flex flex-row-reverse text-sm text-center mb-4">
            <div class="text-black bg-white p-3 flex-1">White</div>
            <div class="text-black bg-grey-lightest p-3 flex-1">Lightest</div>
            <div class="text-black bg-grey-lighter p-3 flex-1">Lighter</div>
            <div class="text-black bg-grey-light p-3 flex-1">Light</div>
            <div class="text-black bg-grey p-3 flex-1">Base</div>
            <div class="text-white bg-grey-dark p-3 flex-1">Dark</div>
            <div class="text-white bg-grey-darker p-3 flex-1">Darker</div>
            <div class="text-white bg-grey-darkest p-3 flex-1">Darkest</div>
            <div class="text-white bg-black p-3 flex-1">Black</div>
        </div>

        <h6 class="mb-2">"Background" Color</h6>
        <div class="flex flex-row-reverse text-sm text-center mb-4">
            <div class="text-black bg-bg-lighter p-3 flex-1">Lighter</div>
            <div class="text-black bg-bg-light p-3 flex-1">Light</div>
            <div class="text-black bg-bg p-3 flex-1">Base</div>
            <div class="text-black bg-bg-dark p-3 flex-1">Dark</div>
        </div>

        <h6 class="mb-2">Actionable</h6>
        <div class="flex text-sm text-center">
            <div class="text-black bg-green p-3 flex-1">Green</div>
            <div class="text-black bg-red p-3 flex-1">Red</div>
            <div class="text-black bg-yellow p-3 flex-1">Yellow</div>
            <div class="text-black bg-yellow-dark p-3 flex-1">Yellow Dark</div>
            <div class="text-black bg-pink p-3 flex-1">Pink</div>
            <div class="text-black bg-purple p-3 flex-1">Purple</div>
        </div>
    </div>
@stop
