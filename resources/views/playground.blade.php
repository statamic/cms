@php
    use function Statamic\trans as __;
    use Illuminate\Support\Facades\File;

    $icons = collect(File::files(base_path('vendor/statamic/cms/resources/svg/icons')))->map(function ($file) {
        return $file->getFilenameWithoutExtension();
    })->all();

@endphp

@extends('statamic::layout')
@section('title', __('Playground'))
@section('wrapper_class', 'max-w-7xl')

@section('content')
<ui-header title="Playground" icon="playground">
    <ui-subheading>A collection of components to test and play with.</ui-subheading>
</ui-header>

<div class="space-y-12">

    <ui-switch />

    <section class="space-y-4">
        <ui-heading size="lg">Badges</ui-heading>
        <div class="mb-4 flex gap-3 items-end">
            <ui-badge size="lg" text="Green" color="green" />
            <ui-badge size="lg" text="Red" color="red" />
            <ui-badge size="lg" text="Black" color="black" />
            <ui-badge text="Blue" color="blue" />
            <ui-badge text="Amber" color="amber" />
            <ui-badge text="Pink" color="pink" />
            <ui-badge size="sm" text="Cyan" color="cyan" />
            <ui-badge size="sm" text="Purple" color="purple" />
            <ui-badge size="sm" text="Gray" color="gray" />
        </div>
    </section>

    <section class="space-y-4">
        <ui-heading size="lg">Calendar</ui-heading>
        <div class="flex gap-6">
            <ui-card>
                <ui-calendar />
            </ui-card>
            <ui-card>
                <ui-calendar :number-of-months="2" />
            </ui-card>
        </div>
    </section>

    <section class="space-y-4">
        <ui-heading size="lg">Card</ui-heading>
        <div class="flex items-center justify-center bg-gray-50 rounded-xl border border-gray-200 p-12">
        <ui-card class="w-80 space-y-6">
            <header>
                <ui-heading size="lg">Create a new account</ui-heading>
                <ui-subheading>Welcome to the thing! You're gonna love it here.</ui-subheading>
            </header>
            <ui-input label="Name" placeholder="Your name" />
            <ui-input label="Email" type="email" placeholder="Your email" />
            <div class="space-y-2 pt-6">
                <ui-button variant="primary" class="w-full" text="Continue" type="submit" />
                <ui-button variant="ghost" class="w-full">Already have an account? Go sign in</ui-button>
            </div>
        </ui-card>
        </div>
    </section>

    <section class="space-y-4">
        <ui-heading size="lg">Card Panel</ui-heading>
        <ui-card-panel heading="Card Panel">This is a card panel.</ui-card-panel>
    </section>

    <section class="space-y-4">
        <ui-heading size="lg">Checkboxes</ui-heading>
        <div class="flex">
            <ui-checkbox-group name="meals" label="Select your favorite meals">
                <ui-checkbox-item
                    label="Breakfast"
                    description="The morning meal. Should include eggs."
                    value="breakfast"
                />
                <ui-checkbox-item label="Lunch" description="The mid-day meal. Should be protein heavy." value="lunch" />
                <ui-checkbox-item label="Dinner" description="The evening meal. Should be delicious." value="dinner" />
            </ui-checkbox-group>
        </div>
    </section>

    <section class="space-y-4">
        <ui-heading size="lg">Datepicker</ui-heading>
        <div class="flex">
            <ui-datepicker />
        </div>
    </section>

    <section class="space-y-4">
        <ui-heading size="lg">Dropdown</ui-heading>
        <div class="flex">
            <ui-dropdown>
                <template #trigger>
                    <ui-button text="Do a Action" variant="filled" icon-append="ui/chevron-vertical" class="[&_svg]:size-2" />
                </template>
            <ui-dropdown-menu>
                <ui-dropdown-item text="Bake a food" />
                <ui-dropdown-item text="Write that book" />
                <ui-dropdown-item text="Eat this meal" />
                <ui-dropdown-item text="Lie about larceny" />
                <ui-dropdown-item text="Save some bird" />
                </ui-dropdown-menu>
            </ui-dropdown>
        </div>
    </section>

    <section class="space-y-4">
        <ui-heading size="lg">Icons</ui-heading>
        <div class="grid grid-cols-4 md:grid-cols-6 2xl:grid-cols-10 gap-4">
            @foreach ($icons as $icon)
                <div class="bg-gray-50 rounded-lg py-6 px-2 flex flex-col items-center gap-3">
                    <ui-icon name="{{ $icon }}" class="size-6" />
                    <span class="text-xs text-gray-500">{{ $icon }}</span>
                </div>
            @endforeach
        </div>
    </section>

    <section class="space-y-4">
        <ui-heading size="lg">Input</ui-heading>
        <div class="flex">
            <ui-input
                name="email"
                type="email"
                required
                label="Email"
                description="We need it so we can sell your info to spammers."
            />
        </div>
    </section>

    <section class="space-y-4">
        <ui-heading size="lg">Modal</ui-heading>
        <div class="flex">
            <ui-modal title="That's Pretty Neat">
                <template #trigger>
                    <ui-button text="How neat is that?" />
                </template>
            </ui-modal>
        </div>
    </section>

    <section class="space-y-4">
        <ui-heading size="lg">Popover</ui-heading>
        <div class="flex">
            <ui-popover>
                <template #trigger>
                    <ui-button text="Open Popover" />
                </template>

                <div class="flex flex-col gap-2.5">
                    <ui-heading text="Provide Feedback" />
                    <ui-textarea placeholder="How we can make this component better?" elastic />
                    <div class="flex flex-col sm:flex-row sm:justify-end">
                        <ui-button variant="primary" size="sm" text="Submit" />
                    </div>
                </div>
            </ui-popover>
        </div>
    </section>

    <section class="space-y-4">
        <ui-heading size="lg">Radio Group</ui-heading>
        <div class="flex">
            <ui-radio-group name="favorite" label="Choose your favorite meal">
                <ui-radio-item
                    label="Breakfast"
                    description="The morning meal. Should include eggs."
                    value="breakfast"
                    checked
            />
            <ui-radio-item label="Lunch" description="The mid-day meal. Should be protein heavy." value="lunch" />
                <ui-radio-item label="Dinner" description="The evening meal Should be delicious." value="dinner" />
            </ui-radio-group>
        </div>
    </section>

    <section class="space-y-4">
        <ui-heading size="lg">Select</ui-heading>
        <div class="flex">
            <ui-select
                class="w-full"
                label="Favorite band"
                :options="[
                    { label: 'The Midnight', value: 'the_midnight' },
                    { label: 'The 1975', value: 'the_1975' },
                    { label: 'Sunglasses Kid', value: 'sunglasses_kid' }
                ]"
            />
        </div>
    </section>

    <section class="space-y-4">
        <ui-heading size="lg">Separator</ui-heading>
        <div class="flex">
            <ui-separator text="vs" />
        </div>
    </section>

    <section class="space-y-4">
        <ui-heading size="lg">Splitter</ui-heading>
        <div class="flex bg-gray-50 rounded-xl p-4 border border-gray-200">
            <ui-splitter-group>
                <ui-splitter-panel class="flex h-24 items-center justify-center rounded-xl bg-white">
                    Left
            </ui-splitter-panel>
            <ui-splitter-resize-handle class="w-3" />
            <ui-splitter-panel class="flex h-24 items-center justify-center rounded-xl bg-white">
                Right
                </ui-splitter-panel>
            </ui-splitter-group>
        </div>
    </section>

    <section class="space-y-4">
        <ui-heading size="lg">Switch</ui-heading>
        <div class="flex items-center gap-2">
            <ui-switch size="sm" />
            <ui-switch  />
            <ui-switch size="lg" />
        </div>
    </section>

    <section class="space-y-4">
        <ui-heading size="lg">Table</ui-heading>
        <div class="flex">
            <ui-table>
                <ui-table-columns>
                    <ui-table-column>Product</ui-table-column>
                <ui-table-column>Stock</ui-table-column>
                <ui-table-column class="text-right">Price</ui-table-column>
            </ui-table-columns>
            <ui-table-rows>
                <ui-table-row>
                    <ui-table-cell>Mechanical Keyboard</ui-table-cell>
                    <ui-table-cell>
                        <ui-badge color="green" pill>In Stock</ui-badge>
                    </ui-table-cell>
                    <ui-table-cell class="text-right font-semibold text-black">$159.00</ui-table-cell>
                </ui-table-row>
                <ui-table-row>
                    <ui-table-cell>Ergonomic Mouse</ui-table-cell>
                    <ui-table-cell>
                        <ui-badge color="red" pill>Out of Stock</ui-badge>
                    </ui-table-cell>
                    <ui-table-cell class="text-right font-semibold text-black">$89.00</ui-table-cell>
                </ui-table-row>
                <ui-table-row>
                    <ui-table-cell>4K Monitor</ui-table-cell>
                    <ui-table-cell>
                        <ui-badge color="yellow" pill>Low Stock</ui-badge>
                    </ui-table-cell>
                    <ui-table-cell class="text-right font-semibold text-black">$349.00</ui-table-cell>
                </ui-table-row>
                </ui-table-rows>
            </ui-table>
        </div>
    </section>

    <section class="space-y-4">
        <ui-heading size="lg">Tabs</ui-heading>
        <div class="flex">
            <ui-tabs default-tab="tab1" class="w-full">
                <ui-tabs-list>
                    <ui-tabs-trigger text="Shiny" name="tab1" />
                    <ui-tabs-trigger text="Happy" name="tab2" />
                    <ui-tabs-trigger text="People" name="tab3" />
                </ui-tabs-list>
                <ui-tabs-content name="tab1">
                    <p class="py-8">Tab 1 content</p>
                </ui-tabs-content>
                <ui-tabs-content name="tab2">
                    <p class="py-8">Tab 2 content</p>
                </ui-tabs-content>
                <ui-tabs-content name="tab3">
                    <p class="py-8">Tab 3 content</p>
                </ui-tabs-content>
            </ui-tabs>
        </div>
    </section>

    <section class="space-y-4">
        <ui-heading size="lg">Textarea</ui-heading>
        <div class="flex">
            <ui-textarea label="Message" />
        </div>
    </section>

    <section class="space-y-4">
        <ui-heading size="lg">Tooltip</ui-heading>
        <div class="flex">
            <ui-tooltip text="Never gonna give you up">
                <ui-button text="Hover me" />
            </ui-tooltip>
        </div>
    </section>

</div>
@endsection
