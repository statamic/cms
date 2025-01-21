@php use function Statamic\trans as __; @endphp

@extends('statamic::layout')
@section('wrapper_class', 'max-w-7xl')

@section('nontent')

    <collection-wizard
        :steps="['Naming', 'Ordering', 'Behavior', 'Content Model', 'Front-End']">
    </collection-wizard>

@stop

@section('content')

    <div class="flex mb-10">
        <h1>{{ __('The Statamic Playground') }}</h1>
    </div>

    <h2 class="mb-2">
        Form Inputs
    </h2>

    <div class="shadow bg-white p-8 rounded-lg mb-16">
        <div class="mb-4">
            <input type="text" placeholder="unstyled">
        </div>
        <div class="mb-4 flex">
            <input type="text" class="input-text" placeholder="v3 style">
            <select class="rtl:mr-2 ltr:ml-2" name="" id="">
                <option value="">Oh hai Mark</option>
            </select>
        </div>
        <div class="mb-4 flex">
            <input type="text" class="input-text" placeholder="v3 style">
            <button class="btn rtl:mr-2 ltr:ml-2">Default Button</button>
            <button class="btn-primary rtl:mr-2 ltr:ml-2">Primary Button</button>
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
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"/></svg>
                 </div>
            </div>
        </div>
        <div class="mb-4">
            <v-select :multiple="true" :options="['Nintendo 64', 'Super Nintendo', 'Nintendo Gameboy', 'Sega Genesis', 'Sega Game Gear', 'Atari 2600']"></v-select>
        </div>
    </div>

    <h2 class="mb-2">Typography</h2>
    <div class="shadow bg-white p-8 rounded-lg overflow-hidden mb-16">
        <h1 class="mb-4">This is first level heading</h1>
        <h2 class="mb-4">This is a second level heading</h2>
        <h3 class="mb-4">This is a third level heading</h3>
        <h4 class="mb-4">This is a fourth level heading</h4>
        <h5 class="mb-4">This is a fifth level heading</h5>
        <h6 class="mb-4">This is a sixth level heading</h6>
        <p>Paragraph text. Lorem ipsum dolor sit amet, consectetur adipisicing elit. Quam error tempore veritatis, laborum, et assumenda? Necessitatibus excepturi enim quidem maxime! Temporibus dolorum fugit aspernatur.
    </div>

    <h2 class="mb-2">Buttons</h2>
    <div class="shadow bg-white p-8 rounded-lg mb-16">
        <h6 class="mb-4">Flavors</h6>
        <div class="mb-8 flex">
            <button class="rtl:ml-4 ltr:mr-4 btn">Default Button</button>
            <button class="rtl:ml-4 ltr:mr-4 btn-primary">Primary Button</button>
            <button class="rtl:ml-4 ltr:mr-4 btn-danger">Danger Button</button>
            <button class="btn-flat">Flat Button</button>
        </div>
        <h6 class="mb-4">With Dropdowns</h6>
        <div class="mb-8 flex">
            <div class="btn-group rtl:ml-4 ltr:mr-4">
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
            <div class="btn-group rtl:ml-4 ltr:mr-4">
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
            <div class="btn-group rtl:ml-4 ltr:mr-4">
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
            <div class="btn-group rtl:ml-4 ltr:mr-4">
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
            <button disabled class="rtl:ml-4 ltr:mr-4 btn disabled">Default Button</button>
            <button disabled class="rtl:ml-4 ltr:mr-4 btn-primary disabled">Primary Button</button>
            <button disabled class="rtl:ml-4 ltr:mr-4 btn-danger disabled">Danger Button</button>
            <button disabled class="btn-flat disabled">Flat Button</button>
        </div>

        <h6 class="mb-4">Large</h6>
        <div class="mb-8 flex">
            <button class="rtl:ml-4 ltr:mr-4 btn btn-lg">Default Button</button>
            <button class="rtl:ml-4 ltr:mr-4 btn-primary btn-lg">Primary Button</button>
            <button class="rtl:ml-4 ltr:mr-4 btn-danger btn-lg">Danger Button</button>
            <button class="btn-flat btn-lg">Flat Button</button>
        </div>

        <h6 class="mb-4">Small</h6>
        <div class="flex">
            <button class="rtl:ml-4 ltr:mr-4 btn btn-xs">Default Button</button>
            <button class="rtl:ml-4 ltr:mr-4 btn-primary btn-xs">Primary Button</button>
            <button class="rtl:ml-4 ltr:mr-4 btn-danger btn-xs">Danger Button</button>
            <button class="btn-flat btn-xs">Flat Button</button>
        </div>
    </div>

    <h2 class="mb-2">Colors</h2>
    <div class="bg-white dark:bg-black p-10 shadow rounded-lg overflow-hidden mb-16">

        <h6 class="mb-4">grays</h6>
        {{-- <div class="p-4" style="background: #48606f"></div> --}}
        <div class="flex flex-row-reverse text-sm text-center mb-8">
            <div class="text-black bg-blue-100 p-4 flex-1">100</div>
            <div class="text-black bg-blue-200 p-4 flex-1">200</div>
            <div class="text-black bg-blue-300 p-4 flex-1">300</div>
            <div class="text-black bg-blue-400 p-4 flex-1">400</div>
            <div class="text-black bg-blue p-4 flex-1">def</div>
            <div class="text-black bg-blue-500 p-4 flex-1">500</div>
            <div class="text-black bg-blue-600 p-4 flex-1">600</div>
            <div class="text-black bg-blue-700 p-4 flex-1">700</div>
            <div class="text-white bg-blue-800 p-4 flex-1">800</div>
            <div class="text-white bg-blue-900 p-4 flex-1">900</div>
        </div>
        <div class="flex flex-row-reverse text-sm text-center mb-8">
            <div class="text-black bg-white p-4 flex-1">White</div>
            <div class="text-black bg-gray-100 p-4 flex-1">100</div>
            <div class="text-black bg-gray-200 p-4 flex-1">200</div>
            <div class="text-black bg-gray-300 p-4 flex-1">300</div>
            <div class="text-black bg-gray-400 p-4 flex-1">400</div>
            <div class="text-black bg-gray-500 p-4 flex-1">500</div>
            <div class="text-black bg-gray-600 p-4 flex-1">600</div>
            <div class="text-black bg-gray-700 p-4 flex-1">700</div>
            <div class="text-white bg-gray-800 p-4 flex-1">800</div>
            <div class="text-white bg-gray-900 p-4 flex-1">900</div>
            {{-- <div class="text-white bg-gray-950 p-4 flex-1">950</div> --}}
            <div class="text-white bg-black p-4 flex-1">Black</div>
        </div>

        <div class="flex flex-row-reverse text-sm text-center mb-8">
            <div class="text-black bg-white p-4 flex-1">White</div>
            <div class="text-black bg-slate-100 p-4 flex-1">100</div>
            <div class="text-black bg-slate-200 p-4 flex-1">200</div>
            <div class="text-black bg-slate-300 p-4 flex-1">300</div>
            <div class="text-black bg-slate-400 p-4 flex-1">400</div>
            <div class="text-black bg-slate-500 p-4 flex-1">500</div>
            <div class="text-black bg-slate-600 p-4 flex-1">600</div>
            <div class="text-black bg-slate-700 p-4 flex-1">700</div>
            <div class="text-white bg-slate-800 p-4 flex-1">800</div>
            <div class="text-white bg-slate-900 p-4 flex-1">900</div>
            <div class="text-white bg-black p-4 flex-1">Black</div>
        </div>

         <h6 class="mb-4">dark mode</h6>
        <div class="flex flex-row-reverse overflow-x-auto text-sm text-center mb-8">
            <div class="text-black bg-white p-4 flex-1">White</div>
            <div class="text-black bg-dark-100 p-4 flex-1">100</div>
            <div class="text-black bg-dark-150 p-4 flex-1">150</div>
            <div class="text-black bg-dark-175 p-4 flex-1">175</div>
            <div class="text-black bg-dark-200 p-4 flex-1">200</div>
            <div class="text-black bg-dark-250 p-4 flex-1">250</div>
            <div class="text-black bg-dark-275 p-4 flex-1">275</div>
            <div class="text-black bg-dark-300 p-4 flex-1">300</div>
            <div class="text-black bg-dark-350 p-4 flex-1">350</div>
            <div class="text-black bg-dark-400 p-4 flex-1">400</div>
            <div class="text-black bg-dark-500 p-4 flex-1">500</div>
            <div class="text-black bg-dark-550 p-4 flex-1">550</div>
            <div class="text-black bg-dark-575 p-4 flex-1">575</div>
            <div class="text-black bg-dark-600 p-4 flex-1">600</div>
            <div class="text-black bg-dark-650 p-4 flex-1">650</div>
            <div class="text-black bg-dark-700 p-4 flex-1">700</div>
            <div class="text-black bg-dark-750 p-4 flex-1">750</div>
            <div class="text-white bg-dark-800 p-4 flex-1">800</div>
            <div class="text-white bg-dark-900 p-4 flex-1">900</div>
            <div class="text-white bg-dark-950 p-4 flex-1">950</div>
            <div class="text-white bg-dark-975 p-4 flex-1">975</div>
            <div class="text-white bg-black p-4 flex-1">Black</div>
        </div>

        <h6 class="mb-4">Other Colors (needs simplifying)</h6>
        <div class="flex text-sm text-center">
            <div class="text-black bg-blue p-6 flex-1">Blue</div>
            <div class="text-black bg-green-600 p-6 flex-1">Green</div>
            <div class="text-black bg-orange-light border border-orange p-6 m-1 flex-1">Orange</div>
            <div class="text-black bg-yellow border border-yellow-dark p-6 m-1 flex-1">Yellow</div>
            <div class="text-black bg-yellow-dark p-6 m-1 flex-1">Yellow Dark</div>
            <div class="text-black bg-pink border border-pink-dark m-1 p-6 flex-1">Pink</div>
            <div class="text-black bg-purple-light border border-purple p-6 flex-1">Purple</div>
        </div>

        <h6 class="my-4">Reds</h6>
        <div class="flex text-sm text-center space-x-1 rtl:space-x-reverse">
            <div class="text-black bg-red-100 border border-red-200 p-6 flex-1">Red Lighter</div>
            <div class="text-black bg-red-400 p-6 flex-1">Red Light</div>
            <div class="text-black bg-red-500 p-6 flex-1">Red</div>
            <div class="text-black bg-red-700 p-6 flex-1">Red Dark</div>
        </div>
    </div>

    <h2 class="mb-2">Widgets</h2>
    <div class="flex flex-wrap -mx-4 mb-8">
        <div class="w-1/3 px-4">
            <div class="card px-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="font-bold text-gray">New Users</h3>
                    <select class="text-xs" name="" id="">
                        <option value="">30 Days</option>
                    </select>
                </div>
                <div class="text-4xl mb-4">89</div>
                <div class="flex items-center ">
                    <span class="w-4 h-4 text-green-500 rtl:ml-2 ltr:mr-2">@cp_svg('icons/light/performance-increase')</span>
                    <span class="leading-none text-sm">8.54% Increase</span>
                </div>
            </div>
        </div>
        <div class="w-1/3 px-4">
            <div class="card px-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="font-bold text-gray">Form Submissions</h3>
                    <select class="text-xs" name="" id="">
                        <option value="">7 Days</option>
                    </select>
                </div>
                <div class="text-4xl mb-4">35</div>
                <div class="flex items-center ">
                    <span class="w-4 h-4 text-green-500 rtl:ml-2 ltr:mr-2">@cp_svg('icons/light/performance-increase')</span>
                    <span class="leading-none text-sm">2.15% Increase</span>
                </div>
            </div>
        </div>
        <div class="w-1/3 px-4">
            <div class="card bg-gray-900 px-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="font-bold text-gray-400">New Users</h3>
                    <select class="text-xs" name="" id="" class="bg-gray-800 border-gray-800 text-gray-400">
                        <option value="">30 Days</option>
                    </select>
                </div>
                <div class="text-4xl mb-4 text-gray-400">251</div>
                <div class="flex items-center ">
                    <span class="w-4 h-4 text-green-500 rtl:ml-2 ltr:mr-2">@cp_svg('icons/light/performance-increase')</span>
                    <span class="leading-none text-gray-400 text-sm">8.54% Increase</span>
                </div>
            </div>
        </div>
    </div>
@stop
