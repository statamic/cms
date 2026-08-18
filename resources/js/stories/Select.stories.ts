import type {Meta, StoryObj} from '@storybook/vue3';
import {Select} from '@ui';
import {icons} from "@/stories/icons";
import {ref} from "vue";

const meta = {
    title: 'Forms/Select',
    component: Select,
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
        'update:modelValue': {
            description: 'Event handler called when the selected option changes.',
            table: {
                category: 'events',
                type: { summary: '(value: string) => void' }
            }
        }
    },
} satisfies Meta<typeof Select>;

export default meta;
type Story = StoryObj<typeof meta>;

const defaultOptions = [
    { label: 'The Midnight', value: 'the_midnight' },
    { label: 'The 1975', value: 'the_1975' },
    { label: 'Sunglasses Kid', value: 'sunglasses_kid' },
    { label: 'FM-84', value: 'fm_84' },
    { label: 'Timecop1983', value: 'timecop1983' },
];

const defaultCode = `
<Select
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
        components: { Select },
        setup() {
            const value = ref(null);
            const options = defaultOptions;
            return { value, options };
        },
        template: `
            <Select
                v-model="value"
                placeholder="Select a band..."
                :options="options"
            />
        `,
    }),
};

const sizesCode = `
<Select size="xs" placeholder="Extra Small" :options="options" />
<Select size="sm" placeholder="Small" :options="options" />
<Select size="base" placeholder="Base" :options="options" />
<Select size="lg" placeholder="Large" :options="options" />
<Select size="xl" placeholder="Extra Large" :options="options" />
`;

export const _Sizes: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: sizesCode },
        },
    },
    render: () => ({
        components: { Select },
        setup() {
            const options = defaultOptions;
            return { options };
        },
        template: `<div class="flex flex-col gap-4">${sizesCode}</div>`,
    }),
};

const variantsCode = `
<Select variant="default" placeholder="Default" :options="options" />
<Select variant="filled" placeholder="Filled" :options="options" />
<Select variant="ghost" placeholder="Ghost" :options="options" />
<Select variant="subtle" placeholder="Subtle" :options="options" />
`;

export const _Variants: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: variantsCode },
        },
    },
    render: () => ({
        components: { Select },
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
        components: { Select },
        setup() {
            const value = ref('the_midnight');
            const options = defaultOptions;
            return { value, options };
        },
        template: `
            <Select
                v-model="value"
                clearable
                placeholder="Select a band..."
                :options="options"
            />
        `,
    }),
};

const iconCode = `
<Select icon="money-bag-dollar" placeholder="Select a currency..." :options="options" />
`;

export const _Icon: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: iconCode },
        },
    },
    render: () => ({
        components: { Select },
        setup() {
            const value = ref('the_midnight');
            return { value };
        },
        template: `
            <Select
                v-model="value"
                icon="money-bag-dollar"
                placeholder="Select a currency..."
                :options="[
                    { label: 'U.S. Dollar', value: 'usd' },
                    { label: 'Euro', value: 'euro' },
                    { label: 'Gold Doubloon', value: 'gold_doublon' }
                ]"
            />
        `,
    }),
};

const optionSlotsCode = `
<Select
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
</Select>
`;

export const _OptionSlots: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: optionSlotsCode },
        },
    },
    render: () => ({
        components: { Select },
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
            <Select
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
            </Select>
        `,
    }),
};

const adaptiveWidthCode = `
<Select
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
        components: { Select },
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
                <Select
                    v-model="value"
                    adaptive-width
                    placeholder="Select..."
                    :options="options"
                />
            </div>
        `,
    }),
};
