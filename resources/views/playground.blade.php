@php
    use function Statamic\trans as __;
@endphp

@extends('statamic::layout')
@section('wrapper_class', 'max-w-7xl')

@section('content')
    <div class="mb-4 flex">
        <ui-button text="Click me" />
    </div>

    <div class="mb-4 flex">
        <ui-badge text="New" color="green" />
    </div>

    <div class="mb-4 flex">
        <ui-card>
            <ui-calendar />
        </ui-card>
    </div>

    <div class="mb-4 flex">
        <ui-card class="w-full space-y-6">
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

    <div class="mb-4 flex">
        <ui-card-panel heading="Card Panel">This is a card panel.</ui-card-panel>
    </div>

    <div class="mb-4 flex">
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

    <div class="mb-4 flex">
        <ui-datepicker />
    </div>

    <div class="mb-4 flex">
        <ui-dropdown>
            <template #trigger>
                <ui-button text="Do a Action" variant="filled" icon-append="chevron-vertical" class="[&_svg]:size-2" />
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

    <div class="mb-4 flex">
        <ui-icon name="download" class="size-6" />
    </div>

    <div class="mb-4 flex">
        <ui-input
            name="email"
            type="email"
            required
            label="Email"
            description="We need it so we can sell your info to spammers."
        />
    </div>

    <div class="mb-4 flex">
        <ui-modal title="That's Pretty Neat">
            <template #trigger>
                <ui-button text="How neat is that?" />
            </template>
        </ui-modal>
    </div>

    <div class="mb-4 flex">
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

    <div class="mb-4 flex">
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

    <div class="mb-4 flex">
        <ui-select
            label="Favorite band"
            :options="[
     { label: 'The Midnight', value: 'the_midnight' },
     { label: 'The 1975', value: 'the_1975' },
     { label: 'Sunglasses Kid', value: 'sunglasses_kid' }
     ]"
        />
    </div>

    <div class="mb-4 flex">
        <ui-separator text="vs" />
    </div>

    <div class="mb-4 flex">
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

    <div class="mb-4 flex">
        <ui-switch label="Make it So" description="Would you like to make it so?" />
    </div>

    <div class="mb-4 flex">
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

    <div class="mb-4 flex">
        <ui-tabs default-tab="tab1" class="text-center">
            <ui-tabs-list>
                <ui-tabs-trigger text="Shiny" name="tab1" />
                <ui-tabs-trigger text="Happy" name="tab2" />
                <ui-tabs-trigger text="People" name="tab3" />
            </ui-tabs-list>
            <ui-tabs-content name="tab1">
                <p>Tab 1 content</p>
            </ui-tabs-content>
            <ui-tabs-content name="tab2">
                <p>Tab 2 content</p>
            </ui-tabs-content>
            <ui-tabs-content name="tab3">
                <p>Tab 3 content</p>
            </ui-tabs-content>
        </ui-tabs>
    </div>

    <div class="mb-4 flex">
        <ui-textarea label="Message" />
    </div>

    <div class="mb-4 flex">
        <ui-tooltip text="Never gonna give you up">
            <ui-button text="Hover me" />
        </ui-tooltip>
    </div>

    <div class="mb-4 flex">
        <ui-icon name="sun" class="size-6" />
    </div>
@endsection
