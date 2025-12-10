import type { Meta, StoryObj } from '@storybook/vue3';
import { Select } from '@ui';

const meta = {
    title: 'Components/Select',
    component: Select,
    argTypes: {
        label: { control: 'text' },
        size: {
            control: 'select',
            options: ['default', 'sm', 'xs'],
        },
        variant: {
            control: 'select',
            options: ['default', 'filled', 'ghost', 'subtle'],
        },
        clearable: { control: 'boolean' },
        icon: { control: 'text' },
        disabled: { control: 'boolean' },
    },
} satisfies Meta<typeof Select>;

export default meta;
type Story = StoryObj<typeof meta>;

const defaultCode = `
<Select
    label="Favorite band"
    :options="[
        { label: 'The Midnight', value: 'the_midnight' },
        { label: 'The 1975', value: 'the_1975' },
        { label: 'Sunglasses Kid', value: 'sunglasses_kid' }
    ]"
/>
`;

export const _DocsIntro: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: defaultCode }
        }
    },
    render: () => ({
        components: { Select },
        template: defaultCode,
    }),
};

const sizesCode = `
<Select
    label="Favorite band"
    size="sm"
    :options="[
        { label: 'The Midnight', value: 'the_midnight' },
        { label: 'The 1975', value: 'the_1975' },
        { label: 'Sunglasses Kid', value: 'sunglasses_kid' }
    ]"
/>
`;

export const _Sizes: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: sizesCode }
        }
    },
    render: () => ({
        components: { Select },
        template: sizesCode,
    }),
};

const variantsCode = `
<Select
    label="Favorite band"
    :options="[
        { label: 'The Midnight', value: 'the_midnight' },
        { label: 'The 1975', value: 'the_1975' },
        { label: 'Sunglasses Kid', value: 'sunglasses_kid' }
    ]"
/>
<Select
    label="Favorite band"
    variant="filled"
    :options="[
        { label: 'The Midnight', value: 'the_midnight' },
        { label: 'The 1975', value: 'the_1975' },
        { label: 'Sunglasses Kid', value: 'sunglasses_kid' }
    ]"
/>
<Select
    label="Favorite band"
    variant="ghost"
    :options="[
        { label: 'The Midnight', value: 'the_midnight' },
        { label: 'The 1975', value: 'the_1975' },
        { label: 'Sunglasses Kid', value: 'sunglasses_kid' }
    ]"
/>
<Select
    label="Favorite band"
    variant="subtle"
    :options="[
        { label: 'The Midnight', value: 'the_midnight' },
        { label: 'The 1975', value: 'the_1975' },
        { label: 'Sunglasses Kid', value: 'sunglasses_kid' }
    ]"
/>
`;

export const _Variants: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: variantsCode }
        }
    },
    render: () => ({
        components: { Select },
        template: variantsCode,
    }),
};

const clearableCode = `
<Select
    label="Favorite band"
    :clearable="true"
    :options="[
        { label: 'The Midnight', value: 'the_midnight' },
        { label: 'The 1975', value: 'the_1975' },
        { label: 'Sunglasses Kid', value: 'sunglasses_kid' }
    ]"
/>
`;

export const _Clearable: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: clearableCode }
        }
    },
    render: () => ({
        components: { Select },
        template: clearableCode,
    }),
};

const iconCode = `
<Select
    icon="money-bag-dollar"
    label="Currency"
    :options="[
        { label: 'U.S. Dollar', value: 'usd' },
        { label: 'Euro', value: 'euro' },
        { label: 'Gold Doubloon', value: 'gold_doublon' }
    ]"
/>
`;

export const _Icon: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: iconCode }
        }
    },
    render: () => ({
        components: { Select },
        template: iconCode,
    }),
};

const customListItemsCode = `
<Select label="Author" :options="[
    { label: 'Tyler Lyle', image: '/assets/tyler.jpg' },
    { label: 'Tim McEwan', image: '/assets/tim.jpg' },
    { label: 'Nikki Flores', image: '/assets/nikki.jpg' },
]">
    <template #option="{ label, image }">
        <img :src="image" class="size-5 rounded-full" />
        <span v-text="label" />
    </template>
</Select>
`;

export const _CustomListItems: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: customListItemsCode }
        }
    },
    render: () => ({
        components: { Select },
        template: customListItemsCode,
    }),
};
