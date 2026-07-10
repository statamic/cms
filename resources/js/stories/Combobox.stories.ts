import type {Meta, StoryObj} from '@storybook/vue3';
import {expect, fn, userEvent, waitFor, within} from 'storybook/test';
import {Combobox} from '@ui';
import {ref} from 'vue';
import {icons} from "@/stories/icons";

const meta = {
    title: 'Forms/Combobox',
    component: Combobox,
    argTypes: {
        icon: {
            control: 'select',
            options: icons,
        },
        size: {
            control: 'select',
            options: ['xs', 'sm', 'base', 'lg', 'xl'],
        },
        variant: {
            control: 'select',
            options: ['default', 'filled', 'ghost', 'subtle'],
        },
        align: {
            control: 'select',
            options: ['start', 'center', 'end'],
        },
        'update:modelValue': {
            description: 'Event handler called when the selected option changes.',
            table: {
                category: 'events',
                type: { summary: '(value: string | string[]) => void' },
            },
        },
        search: {
            description: 'Event handler called when the search query changes.',
            table: {
                category: 'events',
                type: { summary: '(query: string) => void' },
            },
        },
        selected: {
            description: 'Event handler called when an option is selected.',
            table: {
                category: 'events',
                type: { summary: '(value: string) => void' },
            },
        },
        added: {
            description: 'Event handler called when a taggable option is added.',
            table: {
                category: 'events',
                type: { summary: '(value: string) => void' },
            },
        },
    },
} satisfies Meta<typeof Combobox>;

export default meta;
type Story = StoryObj<typeof meta>;

export const Default: Story = {
    render: () => ({
        components: { Combobox },
        setup() {
            const value = ref(null);
            const options = [
                { label: 'The Midnight', value: 'the_midnight' },
                { label: 'The 1975', value: 'the_1975' },
                { label: 'Sunglasses Kid', value: 'sunglasses_kid' },
                { label: 'FM-84', value: 'fm_84' },
                { label: 'Timecop1983', value: 'timecop1983' },
            ];
            return { value, options };
        },
        template: `
            <Combobox
                v-model="value"
                placeholder="Select a band..."
                :options="options"
            />
        `,
    }),
};

const defaultOptions = [
    { label: 'The Midnight', value: 'the_midnight' },
    { label: 'The 1975', value: 'the_1975' },
    { label: 'Sunglasses Kid', value: 'sunglasses_kid' },
    { label: 'FM-84', value: 'fm_84' },
    { label: 'Timecop1983', value: 'timecop1983' },
];

const defaultCode = `
<Combobox
    placeholder="Select a band..."
    :options="[
        { label: 'The Midnight', value: 'the_midnight' },
        { label: 'The 1975', value: 'the_1975' },
        { label: 'Sunglasses Kid', value: 'sunglasses_kid' },
        { label: 'FM-84', value: 'fm_84' },
        { label: 'Timecop1983', value: 'timecop1983' },
    ]"
/>
`;

export const _DocsIntro: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: defaultCode },
        },
    },
    render: () => ({
        components: { Combobox },
        setup() {
            const value = ref(null);
            const options = defaultOptions;
            return { value, options };
        },
        template: `
            <Combobox
                v-model="value"
                placeholder="Select a band..."
                :options="options"
            />
        `,
    }),
};

const sizesCode = `
<Combobox size="xs" placeholder="Extra Small" :options="options" />
<Combobox size="sm" placeholder="Small" :options="options" />
<Combobox size="base" placeholder="Base" :options="options" />
<Combobox size="lg" placeholder="Large" :options="options" />
<Combobox size="xl" placeholder="Extra Large" :options="options" />
`;

export const _Sizes: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: sizesCode },
        },
    },
    render: () => ({
        components: { Combobox },
        setup() {
            const options = defaultOptions;
            return { options };
        },
        template: `<div class="flex flex-col gap-4">${sizesCode}</div>`,
    }),
};

const variantsCode = `
<Combobox variant="default" placeholder="Default" :options="options" />
<Combobox variant="filled" placeholder="Filled" :options="options" />
<Combobox variant="ghost" placeholder="Ghost" :options="options" />
<Combobox variant="subtle" placeholder="Subtle" :options="options" />
`;

export const _Variants: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: variantsCode },
        },
    },
    render: () => ({
        components: { Combobox },
        setup() {
            const options = defaultOptions;
            return { options };
        },
        template: `<div class="flex flex-col gap-4">${variantsCode}</div>`,
    }),
};

const clearableCode = `
<Combobox clearable placeholder="Select a band..." :options="options" />
`;

export const _Clearable: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: clearableCode },
        },
    },
    render: () => ({
        components: { Combobox },
        setup() {
            const value = ref('the_midnight');
            const options = defaultOptions;
            return { value, options };
        },
        template: `
            <Combobox
                v-model="value"
                clearable
                placeholder="Select a band..."
                :options="options"
            />
        `,
    }),
};

const multipleCode = `
<Combobox multiple placeholder="Select bands..." :options="options" />
`;

export const _Multiple: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: multipleCode },
        },
    },
    render: () => ({
        components: { Combobox },
        setup() {
            const value = ref(['the_midnight', 'fm_84']);
            const options = defaultOptions;
            return { value, options };
        },
        template: `
            <Combobox
                v-model="value"
                :multiple="true"
                placeholder="Select bands..."
                :options="options"
            />
        `,
    }),
};

const maxSelectionsCode = `
<Combobox
    multiple
    :max-selections="3"
    placeholder="Select up to 3 bands..."
    :options="options"
/>
`;

export const _MaxSelections: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: maxSelectionsCode },
        },
    },
    render: () => ({
        components: { Combobox },
        setup() {
            const value = ref(['the_midnight', 'fm_84']);
            const options = defaultOptions;
            return { value, options };
        },
        template: `
            <Combobox
                v-model="value"
                :multiple="true"
                :max-selections="3"
                placeholder="Select up to 3 bands..."
                :options="options"
            />
        `,
    }),
};

const taggableCode = `
<Combobox
    multiple
    taggable
    placeholder="Add tags..."
    :options="options"
/>
`;

export const _Taggable: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: taggableCode },
        },
    },
    render: () => ({
        components: { Combobox },
        setup() {
            const value = ref(['the_midnight']);
            const options = defaultOptions;
            return { value, options };
        },
        template: `
            <Combobox
                v-model="value"
                :multiple="true"
                :taggable="true"
                placeholder="Add tags..."
                :options="options"
            />
        `,
    }),
};

const searchDisabledCode = `
<Combobox :searchable="false" placeholder="Select a band..." :options="options" />
`;

export const _SearchDisabled: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: searchDisabledCode },
        },
    },
    render: () => ({
        components: { Combobox },
        setup() {
            const value = ref(null);
            const options = defaultOptions;
            return { value, options };
        },
        template: `
            <Combobox
                v-model="value"
                :searchable="false"
                placeholder="Select a band..."
                :options="options"
            />
        `,
    }),
};

const ignoreFilterCode = `
<Combobox
    :ignore-filter="true"
    placeholder="Server-side filtering..."
    :options="filteredOptions"
    @search="onSearch"
/>
`;

export const _IgnoreFilter: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: ignoreFilterCode },
            description: {
                story: 'When `ignoreFilter` is true, the Combobox will not filter options locally. Use this when you want to handle filtering yourself via the `search` event, such as with server-side filtering.',
            },
        },
    },
    render: () => ({
        components: { Combobox },
        setup() {
            const value = ref(null);
            const allOptions = defaultOptions;
            const filteredOptions = ref([...allOptions]);

            const onSearch = (query: string) => {
                if (!query) {
                    filteredOptions.value = [...allOptions];
                    return;
                }
                filteredOptions.value = allOptions.filter((opt) =>
                    opt.label.toLowerCase().includes(query.toLowerCase())
                );
            };

            return { value, filteredOptions, onSearch };
        },
        template: `
            <Combobox
                v-model="value"
                :ignore-filter="true"
                placeholder="Server-side filtering..."
                :options="filteredOptions"
                @search="onSearch"
            />
        `,
    }),
};

const optionSlotsCode = `
<Combobox 
    placeholder="Select author..." 
    :options="[
        { label: 'Tyler Lyle', image: '/assets/tyler.jpg', value: 'tyler' },
        { label: 'Tim McEwan', image: '/assets/tim.jpg', value: 'tim' },
        { label: 'Nikki Flores', image: '/assets/nikki.jpg', value: 'nikki' },
    ]"
>
    <template #selected-option="{ option }">
        <img :src="option.image" class="size-5 rounded-full" />
        <span v-text="option.label" />
    </template>
    <template #option="{ label, image }">
        <img :src="image" class="size-5 rounded-full" />
        <span v-text="label" />
    </template>
</Combobox>
`;

export const _OptionSlots: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: optionSlotsCode },
        },
    },
    render: () => ({
        components: { Combobox },
        setup() {
            const value = ref('tyler');
            const options = [
                { label: 'Tyler Lyle', image: 'https://i.pravatar.cc/100?u=tyler', value: 'tyler' },
                { label: 'Tim McEwan', image: 'https://i.pravatar.cc/100?u=tim', value: 'tim' },
                { label: 'Nikki Flores', image: 'https://i.pravatar.cc/100?u=nikki', value: 'nikki' },
            ];
            return { value, options };
        },
        template: `
            <Combobox
                v-model="value"
                placeholder="Select author..."
                :options="options"
            >
                <template #selected-option="{ option }">
                    <img :src="option.image" class="size-5 rounded-full" />
                    <span v-text="option.label" />
                </template>
                <template #option="{ label, image }">
                    <img :src="image" class="size-5 rounded-full" />
                    <span v-text="label" />
                </template>
            </Combobox>
        `,
    }),
};

const adaptiveWidthCode = `
<Combobox
    adaptive-width
    placeholder="Select..."
    :options="[
        { label: 'Short', value: 'short' },
        { label: 'A much longer option label', value: 'long' },
        { label: 'An extremely long option that demonstrates adaptive width', value: 'very_long' },
    ]"
/>
`;

export const _AdaptiveWidth: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: adaptiveWidthCode },
        },
    },
    render: () => ({
        components: { Combobox },
        setup() {
            const value = ref(null);
            const options = [
                { label: 'Short', value: 'short' },
                { label: 'A much longer option label', value: 'long' },
                { label: 'An extremely long option that demonstrates adaptive width', value: 'very_long' },
            ];
            return { value, options };
        },
        template: `
            <div class="w-48">
                <Combobox
                    v-model="value"
                    adaptive-width
                    placeholder="Select..."
                    :options="options"
                />
            </div>
        `,
    }),
};

export const TestTriggerWidth: Story = {
    tags: ['!dev', 'test'],
    render: () => ({
        components: { Combobox },
        setup() {
            const value = ref(null);
            return { value, options: defaultOptions };
        },
        template: `<Combobox v-model="value" :options="options" placeholder="Select..." />`,
    }),
    play: async ({ canvasElement }) => {
        const canvas = within(canvasElement);
        const trigger = canvas.getByRole('combobox');

        await userEvent.click(trigger);

        const content = document.querySelector('[data-ui-combobox-content]');
        await expect(content).toBeTruthy();
        expect(content?.classList.contains('w-max')).toBe(false);
    },
};

export const TestAdaptiveWidth: Story = {
    tags: ['!dev', 'test'],
    render: () => ({
        components: { Combobox },
        setup() {
            const value = ref(null);
            const options = [
                { label: 'A very long option label that should make the dropdown wider', value: 'long' },
            ];
            return { value, options };
        },
        template: `<div class="w-48"><Combobox v-model="value" :options="options" adaptive-width placeholder="Select..." /></div>`,
    }),
    play: async ({ canvasElement }) => {
        const canvas = within(canvasElement);
        const trigger = canvas.getByRole('combobox');

        await userEvent.click(trigger);

        const content = document.querySelector('[data-ui-combobox-content]');
        await expect(content).toBeTruthy();
        expect(content?.classList.contains('w-max')).toBe(true);
    },
};

export const TestCanSelectOption: Story = {
    tags: ['!dev', 'test'],
    args: {
        'onUpdate:modelValue': fn(),
    },
    render: (args) => ({
        components: { Combobox },
        setup() {
            const value = ref(null);
            return { value, options: defaultOptions, onUpdate: args['onUpdate:modelValue'] };
        },
        template: `<Combobox v-model="value" :options="options" placeholder="Select..." @update:modelValue="onUpdate" />`,
    }),
    play: async ({ canvasElement, args }) => {
        const canvas = within(canvasElement);
        const trigger = canvas.getByRole('combobox');

        await userEvent.click(trigger);

        const option = await within(document.body).findByText('The Midnight');
        await userEvent.click(option);

        expect(args['onUpdate:modelValue']).toHaveBeenCalledWith('the_midnight');

        await new Promise((r) => setTimeout(r, 100));
        const selectedOption = canvasElement.querySelector('[data-ui-combobox-selected-option]');
        expect(selectedOption?.textContent).toContain('The Midnight');
    },
};

export const TestCanSelectMultipleOptions: Story = {
    tags: ['!dev', 'test'],
    args: {
        'onUpdate:modelValue': fn(),
    },
    render: (args) => ({
        components: { Combobox },
        setup() {
            const value = ref(['the_midnight']);
            return { value, options: defaultOptions, onUpdate: args['onUpdate:modelValue'] };
        },
        template: `<Combobox v-model="value" :options="options" multiple placeholder="Select..." @update:modelValue="onUpdate" />`,
    }),
    play: async ({ canvasElement, args }) => {
        const canvas = within(canvasElement);
        const trigger = canvas.getByRole('combobox');

        await userEvent.click(trigger);

        const option = await within(document.body).findByText('FM-84');
        await userEvent.click(option);

        await expect(args['onUpdate:modelValue']).toHaveBeenCalled();
        const lastCall = args['onUpdate:modelValue'].mock.calls[args['onUpdate:modelValue'].mock.calls.length - 1];
        expect(lastCall[0]).toContain('the_midnight');
        expect(lastCall[0]).toContain('fm_84');

        const selectedOptions = canvasElement.querySelector('[data-ui-combobox-selected-options]');
        expect(selectedOptions).toBeTruthy();
        expect(selectedOptions?.textContent).toContain('The Midnight');
        expect(selectedOptions?.textContent).toContain('FM-84');
    },
};

export const TestDropdownClosesOnSelection: Story = {
    tags: ['!dev', 'test'],
    render: () => ({
        components: { Combobox },
        setup() {
            const value = ref(null);
            return { value, options: defaultOptions };
        },
        template: `<Combobox v-model="value" :options="options" placeholder="Select..." />`,
    }),
    play: async ({ canvasElement }) => {
        const canvas = within(canvasElement);
        const trigger = canvas.getByRole('combobox');

        await userEvent.click(trigger);
        await expect(document.querySelector('[data-ui-combobox-content]')).toBeTruthy();

        const option = await within(document.body).findByText('The Midnight');
        await userEvent.click(option);

        await new Promise((r) => setTimeout(r, 100));
        await expect(document.querySelector('[data-ui-combobox-content]')).toBeFalsy();
    },
};

export const TestCanClearSelection: Story = {
    tags: ['!dev', 'test'],
    args: {
        'onUpdate:modelValue': fn(),
    },
    render: (args) => ({
        components: { Combobox },
        setup() {
            const value = ref('the_midnight');
            return { value, options: defaultOptions, onUpdate: args['onUpdate:modelValue'] };
        },
        template: `<Combobox v-model="value" :options="options" clearable placeholder="Select..." @update:modelValue="onUpdate" />`,
    }),
    play: async ({ canvasElement, args }) => {
        const canvas = within(canvasElement);
        const clearButton = canvas.getByRole('button', { name: /clear/i });

        await userEvent.click(clearButton);

        expect(args['onUpdate:modelValue']).toHaveBeenCalledWith(null);
    },
};

export const TestMaxSelectionsLimit: Story = {
    tags: ['!dev', 'test'],
    render: () => ({
        components: { Combobox },
        setup() {
            const value = ref(['the_midnight', 'the_1975']);
            return { value, options: defaultOptions };
        },
        template: `<Combobox v-model="value" :options="options" multiple :max-selections="2" :close-on-select="false" placeholder="Select..." />`,
    }),
    play: async ({ canvasElement }) => {
        const canvas = within(canvasElement);
        const trigger = canvas.getByRole('combobox');

        await userEvent.click(trigger);

        const disabledOption = document.querySelector('[data-ui-combobox-item="sunglasses_kid"]');
        expect(disabledOption?.hasAttribute('data-disabled')).toBe(true);

        // Deselect one option by clicking it
        const selectedOption = document.querySelector('[data-ui-combobox-item="the_midnight"]');
        await userEvent.click(selectedOption!);

        await waitFor(() => {
            const nowEnabledOption = document.querySelector('[data-ui-combobox-item="sunglasses_kid"]');
            expect(nowEnabledOption).toBeTruthy();
            expect(nowEnabledOption!.hasAttribute('data-disabled')).toBe(false);
        });
    },
};

export const TestCanDeselectOptions: Story = {
    tags: ['!dev', 'test'],
    args: {
        'onUpdate:modelValue': fn(),
    },
    render: (args) => ({
        components: { Combobox },
        setup() {
            const value = ref(['the_midnight', 'fm_84']);
            return { value, options: defaultOptions, onUpdate: args['onUpdate:modelValue'] };
        },
        template: `<Combobox v-model="value" :options="options" multiple placeholder="Select..." @update:modelValue="onUpdate" />`,
    }),
    play: async ({ canvasElement, args }) => {
        const canvas = within(canvasElement);
        const removeButtons = canvas.getAllByRole('button', { name: /remove/i });

        await userEvent.click(removeButtons[0]);

        await expect(args['onUpdate:modelValue']).toHaveBeenCalled();

        const lastCall = args['onUpdate:modelValue'].mock.calls[args['onUpdate:modelValue'].mock.calls.length - 1];
        expect(lastCall[0]).toContain('fm_84');
        expect(lastCall[0]).not.toContain('the_midnight');
    },
};

export const TestCanSearchOptions: Story = {
    tags: ['!dev', 'test'],
    render: () => ({
        components: { Combobox },
        setup() {
            const value = ref(null);
            return { value, options: defaultOptions };
        },
        template: `<Combobox v-model="value" :options="options" placeholder="Select..." />`,
    }),
    play: async ({ canvasElement }) => {
        const canvas = within(canvasElement);
        const trigger = canvas.getByRole('combobox');

        await userEvent.click(trigger);

        const input = document.querySelector('input[type="search"]') as HTMLInputElement;
        await userEvent.type(input, 'midnight');

        await new Promise((r) => setTimeout(r, 100));

        const options = document.querySelectorAll('[data-ui-combobox-item]');
        expect(options.length).toBe(1);
        expect(options[0].getAttribute('data-ui-combobox-item')).toBe('the_midnight');
    },
};

export const TestSearchDisabledWhenNotSearchable: Story = {
    tags: ['!dev', 'test'],
    render: () => ({
        components: { Combobox },
        setup() {
            const value = ref('the_midnight');
            return { value, options: defaultOptions };
        },
        template: `<Combobox v-model="value" :options="options" :searchable="false" placeholder="Select..." />`,
    }),
    play: async ({ canvasElement }) => {
        const input = canvasElement.querySelector('input[type="search"]');
        expect(input).toBeFalsy();
    },
};

export const TestIgnoreFilterDoesNotFilter: Story = {
    tags: ['!dev', 'test'],
    render: () => ({
        components: { Combobox },
        setup() {
            const value = ref(null);
            return { value, options: defaultOptions };
        },
        template: `<Combobox v-model="value" :options="options" ignore-filter placeholder="Select..." />`,
    }),
    play: async ({ canvasElement }) => {
        const canvas = within(canvasElement);
        const trigger = canvas.getByRole('combobox');

        await userEvent.click(trigger);

        const input = document.querySelector('input[type="search"]') as HTMLInputElement;
        await userEvent.type(input, 'xyz');

        await new Promise((r) => setTimeout(r, 100));

        const options = document.querySelectorAll('[data-ui-combobox-item]');
        expect(options.length).toBe(5);
    },
};

export const TestTaggableCanAddOptions: Story = {
    tags: ['!dev', 'test'],
    args: {
        'onUpdate:modelValue': fn(),
        onAdded: fn(),
    },
    render: (args) => ({
        components: { Combobox },
        setup() {
            const value = ref<string[]>([]);
            return { value, options: defaultOptions, onUpdate: args['onUpdate:modelValue'], onAdded: args.onAdded };
        },
        template: `<Combobox v-model="value" :options="options" multiple taggable placeholder="Add tags..." @update:modelValue="onUpdate" @added="onAdded" />`,
    }),
    play: async ({ canvasElement, args }) => {
        const canvas = within(canvasElement);
        const trigger = canvas.getByRole('combobox');

        await userEvent.click(trigger);

        const input = document.querySelector('input[type="search"]') as HTMLInputElement;
        await userEvent.click(input);
        await userEvent.type(input, 'new-tag');
        await userEvent.keyboard('{Enter}');

        await expect(args['onUpdate:modelValue']).toHaveBeenCalled();
        expect(args.onAdded).toHaveBeenCalledWith('new-tag');
    },
};

export const TestDropdownOpensOnSpace: Story = {
    tags: ['!dev', 'test'],
    render: () => ({
        components: { Combobox },
        setup() {
            const value = ref(null);
            return { value, options: defaultOptions };
        },
        template: `<Combobox v-model="value" :options="options" placeholder="Select..." />`,
    }),
    play: async ({ canvasElement }) => {
        const canvas = within(canvasElement);
        const trigger = canvas.getByRole('combobox');

        trigger.focus();
        await userEvent.keyboard(' ');

        await expect(document.querySelector('[data-ui-combobox-content]')).toBeTruthy();
    },
};

export const TestDropdownOpensOnEnter: Story = {
    tags: ['!dev', 'test'],
    render: () => ({
        components: { Combobox },
        setup() {
            const value = ref(null);
            return { value, options: defaultOptions };
        },
        template: `<Combobox v-model="value" :options="options" placeholder="Select..." />`,
    }),
    play: async ({ canvasElement }) => {
        const canvas = within(canvasElement);
        const trigger = canvas.getByRole('combobox');

        trigger.focus();
        await userEvent.keyboard('{Enter}');

        await expect(document.querySelector('[data-ui-combobox-content]')).toBeTruthy();
    },
};

export const TestDropdownClosesOnEscape: Story = {
    tags: ['!dev', 'test'],
    render: () => ({
        components: { Combobox },
        setup() {
            const value = ref(null);
            return { value, options: defaultOptions };
        },
        template: `<Combobox v-model="value" :options="options" placeholder="Select..." />`,
    }),
    play: async ({ canvasElement }) => {
        const canvas = within(canvasElement);
        const trigger = canvas.getByRole('combobox');

        await userEvent.click(trigger);
        await expect(document.querySelector('[data-ui-combobox-content]')).toBeTruthy();

        await userEvent.keyboard('{Escape}');

        await new Promise((r) => setTimeout(r, 100));
        await expect(document.querySelector('[data-ui-combobox-content]')).toBeFalsy();
    },
};

const manyOptions = [
    { label: 'Option 1', value: 'option_1' },
    { label: 'Option 2', value: 'option_2' },
    { label: 'Option 3', value: 'option_3' },
    { label: 'Option 4', value: 'option_4' },
    { label: 'Option 5', value: 'option_5' },
    { label: 'Option 6', value: 'option_6' },
    { label: 'Option 7', value: 'option_7' },
    { label: 'Option 8', value: 'option_8' },
    { label: 'Option 9', value: 'option_9' },
    { label: 'Option 10', value: 'option_10' },
    { label: 'Option 11', value: 'option_11' },
    { label: 'Option 12', value: 'option_12' },
    { label: 'Option 13', value: 'option_13' },
    { label: 'Option 14', value: 'option_14' },
    { label: 'Option 15', value: 'option_15' },
];

export const TestScrollsToSelectedOption: Story = {
    tags: ['!dev', 'test'],
    render: () => ({
        components: { Combobox },
        setup() {
            const value = ref('option_10');
            return { value, options: manyOptions };
        },
        template: `<Combobox v-model="value" :options="options" placeholder="Select..." />`,
    }),
    play: async ({ canvasElement }) => {
        const trigger = canvasElement.querySelector('[data-ui-combobox-trigger]');
        await expect(trigger).toBeTruthy();

        await userEvent.click(trigger!);
        await new Promise((r) => setTimeout(r, 150));

        // The selected option (Option 10) should be highlighted
        const highlightedOption = document.querySelector('[data-ui-combobox-item][data-highlighted]');
        await expect(highlightedOption).toBeTruthy();
        expect(highlightedOption?.getAttribute('data-ui-combobox-item')).toBe('option_10');

        // Arrow down should go to Option 11, not Option 1
        await userEvent.keyboard('{ArrowDown}');
        await new Promise((r) => setTimeout(r, 50));

        const newHighlightedOption = document.querySelector('[data-ui-combobox-item][data-highlighted]');
        expect(newHighlightedOption?.getAttribute('data-ui-combobox-item')).toBe('option_11');

        // Arrow up twice should go to Option 10 then Option 9
        await userEvent.keyboard('{ArrowUp}');
        await userEvent.keyboard('{ArrowUp}');
        await new Promise((r) => setTimeout(r, 50));

        const upHighlightedOption = document.querySelector('[data-ui-combobox-item][data-highlighted]');
        expect(upHighlightedOption?.getAttribute('data-ui-combobox-item')).toBe('option_9');
    },
};

export const TestDisabledStatePreventsInteraction: Story = {
    tags: ['!dev', 'test'],
    args: {
        'onUpdate:modelValue': fn(),
    },
    render: (args) => ({
        components: { Combobox },
        setup() {
            const value = ref('the_midnight');
            return { value, options: defaultOptions, onUpdate: args['onUpdate:modelValue'] };
        },
        template: `<Combobox v-model="value" :options="options" disabled clearable placeholder="Select..." @update:modelValue="onUpdate" />`,
    }),
    play: async ({ canvasElement, args }) => {
        const trigger = canvasElement.querySelector('[data-ui-combobox-trigger]');
        await expect(trigger).toBeTruthy();

        // Click should not open dropdown
        await userEvent.click(trigger!);
        await new Promise((r) => setTimeout(r, 100));
        await expect(document.querySelector('[data-ui-combobox-content]')).toBeFalsy();

        // Clear button should be rendered as disabled and clicks shouldn't trigger updates
        const clearButton = canvasElement.querySelector('[data-ui-combobox-clear-button]');
        await expect(clearButton).toBeTruthy();
        expect((clearButton as HTMLButtonElement).disabled).toBe(true);
        await userEvent.click(clearButton!);
        await new Promise((r) => setTimeout(r, 100));

        // Model value should not have changed
        await expect(args['onUpdate:modelValue']).not.toHaveBeenCalled();
    },
};

export const TestDisabledStateMultiplePreventsInteraction: Story = {
    tags: ['!dev', 'test'],
    args: {
        'onUpdate:modelValue': fn(),
    },
    render: (args) => ({
        components: { Combobox },
        setup() {
            const value = ref(['the_midnight', 'fm_84']);
            return { value, options: defaultOptions, onUpdate: args['onUpdate:modelValue'] };
        },
        template: `<Combobox v-model="value" :options="options" disabled multiple placeholder="Select..." @update:modelValue="onUpdate" />`,
    }),
    play: async ({ canvasElement, args }) => {
        const trigger = canvasElement.querySelector('[data-ui-combobox-trigger]');
        await expect(trigger).toBeTruthy();

        // Click should not open dropdown
        await userEvent.click(trigger!);
        await new Promise((r) => setTimeout(r, 100));
        await expect(document.querySelector('[data-ui-combobox-content]')).toBeFalsy();

        // Remove buttons should not exist on badges when disabled
        const removeButtons = canvasElement.querySelectorAll('[data-ui-combobox-selected-options] button[aria-label*="Remove"]');
        expect(removeButtons.length).toBe(0);

        // Model value should not have changed
        await expect(args['onUpdate:modelValue']).not.toHaveBeenCalled();
    },
};

export const TestShouldOpenDropdownDoesNotBlockClose: Story = {
    tags: ['!dev', 'test'],
    render: () => ({
        components: { Combobox },
        setup() {
            const value = ref<string[]>([]);
            return { value, options: defaultOptions };
        },
        template: `<Combobox v-model="value" :options="options" multiple taggable :should-open-dropdown="(open) => open && options.length > 0" placeholder="Add tags..." />`,
    }),
    play: async ({ canvasElement }) => {
        const canvas = within(canvasElement);
        const trigger = canvas.getByRole('combobox');

        await userEvent.click(trigger);
        await expect(document.querySelector('[data-ui-combobox-content]')).toBeTruthy();

        await userEvent.keyboard('{Escape}');
        await new Promise((r) => setTimeout(r, 100));

        await expect(document.querySelector('[data-ui-combobox-content]')).toBeFalsy();
    },
};
