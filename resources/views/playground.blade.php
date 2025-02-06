@php
    use function Statamic\trans as __;
@endphp

@extends('statamic::layout')
@section('wrapper_class', 'max-w-7xl')

@section('nontent')

<collection-wizard :steps="['Naming', 'Ordering', 'Behavior', 'Content Model', 'Front-End']"></collection-wizard>

@stop

@section('content')

<div class="mb-10 flex">
    <h1>{{ __('The Statamic Playground') }}</h1>
</div>

<h2 class="mb-2">Form Inputs</h2>

<div class="mb-16 rounded-lg bg-white p-8 shadow">
    <div class="mb-4">
        <input type="text" placeholder="unstyled" />
    </div>
    <div class="mb-4 flex">
        <input type="text" class="input-text" placeholder="v3 style" />
        <select class="ltr:ml-2 rtl:mr-2" name="" id="">
            <option value="">Oh hai Mark</option>
        </select>
    </div>
    <div class="mb-4 flex">
        <input type="text" class="input-text" placeholder="v3 style" />
        <button class="btn ltr:ml-2 rtl:mr-2">Default Button</button>
        <button class="btn-primary ltr:ml-2 rtl:mr-2">Primary Button</button>
    </div>
    <div class="mb-4">
        <textarea name="" class="input-text" placeholder="v3 style"></textarea>
    </div>
    <div class="mb-4">
        <div class="select-input-container w-64">
            <select class="select-input">
                <option value="">Oh hai Mark</option>
                <option value="">I did not do it i did not</option>
            </select>
            <div class="select-input-toggle">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                    <path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z" />
                </svg>
            </div>
        </div>
    </div>
    <div class="mb-4">
        <v-select
            :multiple="true"
            :options="['Nintendo 64', 'Super Nintendo', 'Nintendo Gameboy', 'Sega Genesis', 'Sega Game Gear', 'Atari 2600']"
        ></v-select>
    </div>
</div>

<h2 class="mb-2">Typography</h2>
<div class="mb-16 overflow-hidden rounded-lg bg-white p-8 shadow">
    <h1 class="mb-4">This is first level heading</h1>
    <h2 class="mb-4">This is a second level heading</h2>
    <h3 class="mb-4">This is a third level heading</h3>
    <h4 class="mb-4">This is a fourth level heading</h4>
    <h5 class="mb-4">This is a fifth level heading</h5>
    <h6 class="mb-4">This is a sixth level heading</h6>
    <p>
        Paragraph text. Lorem ipsum dolor sit amet, consectetur adipisicing elit. Quam error tempore veritatis, laborum,
        et assumenda? Necessitatibus excepturi enim quidem maxime! Temporibus dolorum fugit aspernatur.
    </p>
</div>

<h2 class="mb-2">Buttons</h2>
<div class="mb-16 rounded-lg bg-white p-8 shadow">
    <h6 class="mb-4">Flavors</h6>
    <div class="mb-8 flex">
        <button class="btn ltr:mr-4 rtl:ml-4">Default Button</button>
        <button class="btn-primary ltr:mr-4 rtl:ml-4">Primary Button</button>
        <button class="btn-danger ltr:mr-4 rtl:ml-4">Danger Button</button>
        <button class="btn-flat">Flat Button</button>
    </div>
    <h6 class="mb-4">With Dropdowns</h6>
    <div class="mb-8 flex">
        <div class="btn-group ltr:mr-4 rtl:ml-4">
            <button class="btn">Default Button</button>
            <dropdown-list>
                <template v-slot:trigger>
                    <button class="btn">
                        <svg-icon name="micro/chevron-down-xs" class="w-2" />
                    </button>
                </template>
                <li>
                    <dropdown-item text="Item 1" redirect="https://example.com"></dropdown-item>
                    <dropdown-item text="Item 2" redirect="https://example2.com"></dropdown-item>
                </li>
            </dropdown-list>
        </div>
        <div class="btn-group ltr:mr-4 rtl:ml-4">
            <button class="btn-primary">Default Button</button>
            <dropdown-list>
                <template v-slot:trigger>
                    <button class="btn-primary">
                        <svg-icon name="micro/chevron-down-xs" class="w-2" />
                    </button>
                </template>
                <li>
                    <dropdown-item text="Item 1" redirect="https://example.com"></dropdown-item>
                    <dropdown-item text="Item 2" redirect="https://example2.com"></dropdown-item>
                </li>
            </dropdown-list>
        </div>
        <div class="btn-group ltr:mr-4 rtl:ml-4">
            <button class="btn-danger">Default Button</button>
            <dropdown-list>
                <template v-slot:trigger>
                    <button class="btn-danger">
                        <svg-icon name="micro/chevron-down-xs" class="w-2" />
                    </button>
                </template>
                <li>
                    <dropdown-item text="Item 1" redirect="https://example.com"></dropdown-item>
                    <dropdown-item text="Item 2" redirect="https://example2.com"></dropdown-item>
                </li>
            </dropdown-list>
        </div>
        <div class="btn-group ltr:mr-4 rtl:ml-4">
            <button class="btn-flat">Default Button</button>
            <dropdown-list>
                <template v-slot:trigger>
                    <button class="btn-flat">
                        <svg-icon name="micro/chevron-down-xs" class="w-2" />
                    </button>
                </template>
                <li>
                    <dropdown-item text="Item 1" redirect="https://example.com"></dropdown-item>
                    <dropdown-item text="Item 2" redirect="https://example2.com"></dropdown-item>
                </li>
            </dropdown-list>
        </div>
    </div>
    <h6 class="mb-4">Disabled States</h6>
    <div class="mb-8 flex">
        <button disabled class="btn disabled ltr:mr-4 rtl:ml-4">Default Button</button>
        <button disabled class="btn-primary disabled ltr:mr-4 rtl:ml-4">Primary Button</button>
        <button disabled class="btn-danger disabled ltr:mr-4 rtl:ml-4">Danger Button</button>
        <button disabled class="btn-flat disabled">Flat Button</button>
    </div>

    <h6 class="mb-4">Large</h6>
    <div class="mb-8 flex">
        <button class="btn btn-lg ltr:mr-4 rtl:ml-4">Default Button</button>
        <button class="btn-primary btn-lg ltr:mr-4 rtl:ml-4">Primary Button</button>
        <button class="btn-danger btn-lg ltr:mr-4 rtl:ml-4">Danger Button</button>
        <button class="btn-flat btn-lg">Flat Button</button>
    </div>

    <h6 class="mb-4">Small</h6>
    <div class="flex">
        <button class="btn btn-xs ltr:mr-4 rtl:ml-4">Default Button</button>
        <button class="btn-primary btn-xs ltr:mr-4 rtl:ml-4">Primary Button</button>
        <button class="btn-danger btn-xs ltr:mr-4 rtl:ml-4">Danger Button</button>
        <button class="btn-flat btn-xs">Flat Button</button>
    </div>
</div>

<h2 class="mb-2">Colors</h2>
<div class="mb-16 overflow-hidden rounded-lg bg-white p-10 shadow dark:bg-black">
    <h6 class="mb-4">grays</h6>
    {{-- <div class="p-4" style="background: #48606f"></div> --}}
    <div class="mb-8 flex flex-row-reverse text-center text-sm">
        <div class="flex-1 bg-blue-100 p-4 text-black">100</div>
        <div class="flex-1 bg-blue-200 p-4 text-black">200</div>
        <div class="flex-1 bg-blue-300 p-4 text-black">300</div>
        <div class="flex-1 bg-blue-400 p-4 text-black">400</div>
        <div class="flex-1 bg-blue p-4 text-black">def</div>
        <div class="flex-1 bg-blue-500 p-4 text-black">500</div>
        <div class="flex-1 bg-blue-600 p-4 text-black">600</div>
        <div class="flex-1 bg-blue-700 p-4 text-black">700</div>
        <div class="flex-1 bg-blue-800 p-4 text-white">800</div>
        <div class="flex-1 bg-blue-900 p-4 text-white">900</div>
    </div>
    <div class="mb-8 flex flex-row-reverse text-center text-sm">
        <div class="flex-1 bg-white p-4 text-black">White</div>
        <div class="flex-1 bg-gray-100 p-4 text-black">100</div>
        <div class="flex-1 bg-gray-200 p-4 text-black">200</div>
        <div class="flex-1 bg-gray-300 p-4 text-black">300</div>
        <div class="flex-1 bg-gray-400 p-4 text-black">400</div>
        <div class="flex-1 bg-gray-500 p-4 text-black">500</div>
        <div class="flex-1 bg-gray-600 p-4 text-black">600</div>
        <div class="flex-1 bg-gray-700 p-4 text-black">700</div>
        <div class="flex-1 bg-gray-800 p-4 text-white">800</div>
        <div class="flex-1 bg-gray-900 p-4 text-white">900</div>
        {{-- <div class="text-white bg-gray-950 p-4 flex-1">950</div> --}}
        <div class="flex-1 bg-black p-4 text-white">Black</div>
    </div>

    <div class="mb-8 flex flex-row-reverse text-center text-sm">
        <div class="flex-1 bg-white p-4 text-black">White</div>
        <div class="bg-slate-100 flex-1 p-4 text-black">100</div>
        <div class="bg-slate-200 flex-1 p-4 text-black">200</div>
        <div class="bg-slate-300 flex-1 p-4 text-black">300</div>
        <div class="bg-slate-400 flex-1 p-4 text-black">400</div>
        <div class="bg-slate-500 flex-1 p-4 text-black">500</div>
        <div class="bg-slate-600 flex-1 p-4 text-black">600</div>
        <div class="bg-slate-700 flex-1 p-4 text-black">700</div>
        <div class="bg-slate-800 flex-1 p-4 text-white">800</div>
        <div class="bg-slate-900 flex-1 p-4 text-white">900</div>
        <div class="flex-1 bg-black p-4 text-white">Black</div>
    </div>

    <h6 class="mb-4">dark mode</h6>
    <div class="mb-8 flex flex-row-reverse overflow-x-auto text-center text-sm">
        <div class="flex-1 bg-white p-4 text-black">White</div>
        <div class="flex-1 bg-dark-100 p-4 text-black">100</div>
        <div class="flex-1 bg-dark-150 p-4 text-black">150</div>
        <div class="flex-1 bg-dark-175 p-4 text-black">175</div>
        <div class="flex-1 bg-dark-200 p-4 text-black">200</div>
        <div class="flex-1 bg-dark-250 p-4 text-black">250</div>
        <div class="flex-1 bg-dark-275 p-4 text-black">275</div>
        <div class="flex-1 bg-dark-300 p-4 text-black">300</div>
        <div class="flex-1 bg-dark-350 p-4 text-black">350</div>
        <div class="flex-1 bg-dark-400 p-4 text-black">400</div>
        <div class="flex-1 bg-dark-500 p-4 text-black">500</div>
        <div class="flex-1 bg-dark-550 p-4 text-black">550</div>
        <div class="flex-1 bg-dark-575 p-4 text-black">575</div>
        <div class="flex-1 bg-dark-600 p-4 text-black">600</div>
        <div class="flex-1 bg-dark-650 p-4 text-black">650</div>
        <div class="flex-1 bg-dark-700 p-4 text-black">700</div>
        <div class="flex-1 bg-dark-750 p-4 text-black">750</div>
        <div class="flex-1 bg-dark-800 p-4 text-white">800</div>
        <div class="flex-1 bg-dark-900 p-4 text-white">900</div>
        <div class="flex-1 bg-dark-950 p-4 text-white">950</div>
        <div class="flex-1 bg-dark-975 p-4 text-white">975</div>
        <div class="flex-1 bg-black p-4 text-white">Black</div>
    </div>

    <h6 class="mb-4">Other Colors (needs simplifying)</h6>
    <div class="flex text-center text-sm">
        <div class="flex-1 bg-blue p-6 text-black">Blue</div>
        <div class="flex-1 bg-green-600 p-6 text-black">Green</div>
        <div class="m-1 flex-1 border border-orange bg-orange-light p-6 text-black">Orange</div>
        <div class="m-1 flex-1 border border-yellow-dark bg-yellow p-6 text-black">Yellow</div>
        <div class="m-1 flex-1 bg-yellow-dark p-6 text-black">Yellow Dark</div>
        <div class="m-1 flex-1 border border-pink-dark bg-pink p-6 text-black">Pink</div>
        <div class="flex-1 border border-purple bg-purple-light p-6 text-black">Purple</div>
    </div>

    <h6 class="my-4">Reds</h6>
    <div class="flex space-x-1 text-center text-sm rtl:space-x-reverse">
        <div class="flex-1 border border-red-200 bg-red-100 p-6 text-black">Red Lighter</div>
        <div class="flex-1 bg-red-400 p-6 text-black">Red Light</div>
        <div class="flex-1 bg-red-500 p-6 text-black">Red</div>
        <div class="flex-1 bg-red-700 p-6 text-black">Red Dark</div>
    </div>
</div>

<h2 class="mb-2">Widgets</h2>
<div class="-mx-4 mb-8 flex flex-wrap">
    <div class="w-1/3 px-4">
        <div class="card px-6">
            <div class="mb-4 flex items-center justify-between">
                <h3 class="font-bold text-gray">New Users</h3>
                <select class="text-xs" name="" id="">
                    <option value="">30 Days</option>
                </select>
            </div>
            <div class="mb-4 text-4xl">89</div>
            <div class="flex items-center">
                <span class="h-4 w-4 text-green-500 ltr:mr-2 rtl:ml-2">
                    @cp_svg('icons/light/performance-increase')
                </span>
                <span class="text-sm leading-none">8.54% Increase</span>
            </div>
        </div>
    </div>
    <div class="w-1/3 px-4">
        <div class="card px-6">
            <div class="mb-4 flex items-center justify-between">
                <h3 class="font-bold text-gray">Form Submissions</h3>
                <select class="text-xs" name="" id="">
                    <option value="">7 Days</option>
                </select>
            </div>
            <div class="mb-4 text-4xl">35</div>
            <div class="flex items-center">
                <span class="h-4 w-4 text-green-500 ltr:mr-2 rtl:ml-2">
                    @cp_svg('icons/light/performance-increase')
                </span>
                <span class="text-sm leading-none">2.15% Increase</span>
            </div>
        </div>
    </div>
    <div class="w-1/3 px-4">
        <div class="card bg-gray-900 px-6">
            <div class="mb-4 flex items-center justify-between">
                <h3 class="font-bold text-gray-400">New Users</h3>
                <select class="text-xs" name="" id="" class="border-gray-800 bg-gray-800 text-gray-400">
                    <option value="">30 Days</option>
                </select>
            </div>
            <div class="mb-4 text-4xl text-gray-400">251</div>
            <div class="flex items-center">
                <span class="h-4 w-4 text-green-500 ltr:mr-2 rtl:ml-2">
                    @cp_svg('icons/light/performance-increase')
                </span>
                <span class="text-sm leading-none text-gray-400">8.54% Increase</span>
            </div>
        </div>
    </div>
</div>
@stop
