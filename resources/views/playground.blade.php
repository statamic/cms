@extends('statamic::layout')

@section('nontent')

    <collection-wizard
        :steps="['Naming', 'Ordering', 'Behavior', 'Content Model', 'Front-End']">
    </collection-wizard>

@stop

@section('content')

    <div class="flex mb-10___REPLACED">
        <h1>{{ __('The Statamic Playground') }}</h1>
    </div>

    <h2 class="mb-2___REPLACED">
        Form Inputs
    </h2>

    <div class="shadow bg-white p-8___REPLACED rounded-lg mb-16___REPLACED">
        <div class="mb-4___REPLACED">
            <input type="text" placeholder="unstyled">
        </div>
        <div class="mb-4___REPLACED flex">
            <input type="text" class="input-text" placeholder="v3 style">
            <select class="ml-2___REPLACED" name="" id="">
                <option value="">Oh hai Mark</option>
            </select>
        </div>
        <div class="mb-4___REPLACED flex">
            <input type="text" class="input-text" placeholder="v3 style">
            <button class="btn ml-2___REPLACED">Default Button</button>
            <button class="btn-primary ml-2___REPLACED">Primary Button</button>
        </div>
        <div class="mb-4___REPLACED">
            <textarea name="" class="input-text" placeholder="v3 style"></textarea>
        </div>
        <div class="mb-4___REPLACED">
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
        <div class="mb-4___REPLACED">
            <v-select :multiple="true" :options="['Nintendo 64', 'Super Nintendo', 'Nintendo Gameboy', 'Sega Genesis', 'Sega Game Gear', 'Atari 2600']"></v-select>
        </div>
    </div>

    <h2 class="mb-2___REPLACED">Typography</h2>
    <div class="shadow bg-white p-8___REPLACED rounded-lg overflow-hidden mb-16___REPLACED">
        <h1 class="mb-4___REPLACED">This is first level heading</h1>
        <h2 class="mb-4___REPLACED">This is a second level heading</h2>
        <h3 class="mb-4___REPLACED">This is a third level heading</h3>
        <h4 class="mb-4___REPLACED">This is a fourth level heading</h4>
        <h5 class="mb-4___REPLACED">This is a fifth level heading</h5>
        <h6 class="mb-4___REPLACED">This is a sixth level heading</h6>
        <p>Paragraph text. Lorem ipsum dolor sit amet, consectetur adipisicing elit. Quam error tempore veritatis, laborum, et assumenda? Necessitatibus excepturi enim quidem maxime! Temporibus dolorum fugit aspernatur.
    </div>

    <h2 class="mb-2___REPLACED">Buttons</h2>
    <div class="shadow bg-white p-8___REPLACED rounded-lg mb-16___REPLACED">
        <h6 class="mb-4___REPLACED">Flavors</h6>
        <div class="mb-8___REPLACED flex">
            <button class="mr-4___REPLACED btn">Default Button</button>
            <button class="mr-4___REPLACED btn-primary">Primary Button</button>
            <button class="mr-4___REPLACED btn-danger">Danger Button</button>
            <button class="btn-flat">Flat Button</button>
        </div>
        <h6 class="mb-4___REPLACED">With Dropdowns</h6>
        <div class="mb-8___REPLACED flex">
            <div class="btn-group mr-4___REPLACED">
                <button class="btn">Default Button</button>
                <dropdown-list>
                    <template v-slot:trigger>
                        <button class="btn">
                            <svg-icon name="chevron-down-xs" class="w-2" />
                        </button>
                    </template>
                    <li>
                        <dropdown-item text="Item 1" redirect="https://example.com"></dropdown-item>
                        <dropdown-item text="Item 2" redirect="https://example2.com"></dropdown-item>
                    </li>
                </dropdown-list>
            </div>
            <div class="btn-group mr-4___REPLACED">
                <button class="btn-primary">Default Button</button>
                <dropdown-list>
                    <template v-slot:trigger>
                        <button class="btn-primary">
                            <svg-icon name="chevron-down-xs" class="w-2" />
                        </button>
                    </template>
                    <li>
                        <dropdown-item text="Item 1" redirect="https://example.com"></dropdown-item>
                        <dropdown-item text="Item 2" redirect="https://example2.com"></dropdown-item>
                    </li>
                </dropdown-list>
            </div>
            <div class="btn-group mr-4___REPLACED">
                <button class="btn-danger">Default Button</button>
                <dropdown-list>
                    <template v-slot:trigger>
                        <button class="btn-danger">
                            <svg-icon name="chevron-down-xs" class="w-2" />
                        </button>
                    </template>
                    <li>
                        <dropdown-item text="Item 1" redirect="https://example.com"></dropdown-item>
                        <dropdown-item text="Item 2" redirect="https://example2.com"></dropdown-item>
                    </li>
                </dropdown-list>
            </div>
            <div class="btn-group mr-4___REPLACED">
                <button class="btn-flat">Default Button</button>
                <dropdown-list>
                    <template v-slot:trigger>
                        <button class="btn-flat">
                            <svg-icon name="chevron-down-xs" class="w-2" />
                        </button>
                    </template>
                    <li>
                        <dropdown-item text="Item 1" redirect="https://example.com"></dropdown-item>
                        <dropdown-item text="Item 2" redirect="https://example2.com"></dropdown-item>
                    </li>
                </dropdown-list>
            </div>
        </div>
        <h6 class="mb-4___REPLACED">Disabled States</h6>
        <div class="mb-8___REPLACED flex">
            <button disabled class="mr-4___REPLACED btn disabled">Default Button</button>
            <button disabled class="mr-4___REPLACED btn-primary disabled">Primary Button</button>
            <button disabled class="mr-4___REPLACED btn-danger disabled">Danger Button</button>
            <button disabled class="btn-flat disabled">Flat Button</button>
        </div>

        <h6 class="mb-4___REPLACED">Large</h6>
        <div class="mb-8___REPLACED flex">
            <button class="mr-4___REPLACED btn btn-lg">Default Button</button>
            <button class="mr-4___REPLACED btn-primary btn-lg">Primary Button</button>
            <button class="mr-4___REPLACED btn-danger btn-lg">Danger Button</button>
            <button class="btn-flat btn-lg">Flat Button</button>
        </div>

        <h6 class="mb-4___REPLACED">Small</h6>
        <div class="flex">
            <button class="mr-4___REPLACED btn btn-sm">Default Button</button>
            <button class="mr-4___REPLACED btn-primary btn-sm">Primary Button</button>
            <button class="mr-4___REPLACED btn-danger btn-sm">Danger Button</button>
            <button class="btn-flat btn-sm">Flat Button</button>
        </div>
    </div>

    <h2 class="mb-2___REPLACED">Colors</h2>
    <div class="bg-white p-10___REPLACED shadow rounded-lg overflow-hidden mb-16___REPLACED">

        <h6 class="mb-4___REPLACED">Greys</h6>
        <div class="flex flex-row-reverse text-sm text-center mb-8___REPLACED">
            <div class="text-black bg-white p-4___REPLACED flex-1">White</div>
            <div class="text-black bg-grey-10 p-4___REPLACED flex-1">10</div>
            <div class="text-black bg-grey-20 p-4___REPLACED flex-1">20</div>
            <div class="text-black bg-grey-30 p-4___REPLACED flex-1">30</div>
            <div class="text-black bg-grey-40 p-4___REPLACED flex-1">40</div>
            <div class="text-black bg-grey-50 p-4___REPLACED flex-1">50</div>
            <div class="text-black bg-grey-60 p-4___REPLACED flex-1">60</div>
            <div class="text-black bg-grey-70 p-4___REPLACED flex-1">70</div>
            <div class="text-white bg-grey-80 p-4___REPLACED flex-1">80</div>
            <div class="text-white bg-grey-90 p-4___REPLACED flex-1">90</div>
            <div class="text-white bg-grey-100 p-4___REPLACED flex-1">100</div>
            <div class="text-white bg-black p-4___REPLACED flex-1">Black</div>
        </div>

        <h6 class="mb-4___REPLACED">Other Colors (needs simplifying)</h6>
        <div class="flex text-sm text-center">
            <div class="text-black bg-blue p-6___REPLACED flex-1">Blue</div>
            <div class="text-black bg-green p-6___REPLACED flex-1">Green</div>
            <div class="text-black bg-red p-6___REPLACED flex-1">Red</div>
            <div class="text-black bg-yellow p-6___REPLACED flex-1">Yellow</div>
            <div class="text-black bg-yellow-dark p-6___REPLACED flex-1">Yellow Dark</div>
            <div class="text-black bg-pink p-6___REPLACED flex-1">Pink</div>
            <div class="text-black bg-purple p-6___REPLACED flex-1">Purple</div>
        </div>
    </div>

    <h2 class="mb-2___REPLACED">Widgets</h2>
    <div class="flex flex-wrap -mx-2 mb-8___REPLACED">
        <div class="w-1/3 px-2">
            <div class="card px-3">
                <div class="flex justify-between items-center mb-4___REPLACED">
                    <h3 class="font-bold text-grey">New Users</h3>
                    <select class="text-xs" name="" id="">
                        <option value="">30 Days</option>
                    </select>
                </div>
                <div class="text-4xl mb-4___REPLACED">89</div>
                <div class="flex items-center ">
                    <span class="w-4 h-4 text-green mr-2___REPLACED">@cp_svg('performance-increase')</span>
                    <span class="leading-none text-sm">8.54% Increase</span>
                </div>
            </div>
        </div>
        <div class="w-1/3 px-2">
            <div class="card px-3">
                <div class="flex justify-between items-center mb-4___REPLACED">
                    <h3 class="font-bold text-grey">Form Submissions</h3>
                    <select class="text-xs" name="" id="">
                        <option value="">7 Days</option>
                    </select>
                </div>
                <div class="text-4xl mb-4___REPLACED">35</div>
                <div class="flex items-center ">
                    <span class="w-4 h-4 text-green mr-2___REPLACED">@cp_svg('performance-increase')</span>
                    <span class="leading-none text-sm">2.15% Increase</span>
                </div>
            </div>
        </div>
        <div class="w-1/3 px-2">
            <div class="card bg-grey-90 px-3">
                <div class="flex justify-between items-center mb-4___REPLACED">
                    <h3 class="font-bold text-grey-40">New Users</h3>
                    <select class="text-xs" name="" id="" class="bg-grey-80 border-grey-80 text-grey-40">
                        <option value="">30 Days</option>
                    </select>
                </div>
                <div class="text-4xl mb-4___REPLACED text-grey-40">251</div>
                <div class="flex items-center ">
                    <span class="w-4 h-4 text-green mr-2___REPLACED">@cp_svg('performance-increase')</span>
                    <span class="leading-none text-grey-40 text-sm">8.54% Increase</span>
                </div>
            </div>
        </div>
    </div>
@stop
