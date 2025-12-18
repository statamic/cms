import type { Meta, StoryObj } from '@storybook/vue3';
import { Checkbox, CheckboxGroup } from '@ui';
import { ref } from 'vue';

const meta = {
    title: 'Components/Checkbox',
    component: Checkbox,
    subcomponents: { CheckboxGroup },
    argTypes: {
        modelValue: {
            control: 'boolean',
            description: 'The checked state of the checkbox.',
        },
        label: {
            control: 'text',
            description: 'Label text to display next to the checkbox.',
        },
        description: {
            control: 'text',
            description: 'Description text to display below the label.',
        },
        value: {
            control: 'text',
            description: 'Value of the checkbox when used in a group.',
        },
        disabled: {
            control: 'boolean',
            description: 'When `true`, disables the checkbox.',
        },
        readOnly: {
            control: 'boolean',
            description: 'When `true`, makes the checkbox read-only.',
        },
        size: {
            control: 'select',
            options: ['sm', 'base'],
            description: 'Controls the size of the checkbox.',
        },
        align: {
            control: 'select',
            options: ['start', 'center'],
            description: 'Controls the vertical alignment of the checkbox with its label.',
        },
        solo: {
            control: 'boolean',
            description: 'When `true`, hides the label and description. Use this when the checkbox is used in a context where the label is provided elsewhere, like in a table cell.',
        },
        name: {
            control: 'text',
            description: 'Name attribute for the checkbox input.',
        },
        'update:modelValue': {
            description: 'Event handler called when the checkbox is checked or unchecked.',
            table: {
                category: 'events',
                type: { summary: '(checked: boolean) => void' },
            },
        },
        default: {
            control: false,
            description: 'Content to display as the label.',
            table: { category: 'slots' },
        },
    },
} satisfies Meta<typeof Checkbox>;

export default meta;
type Story = StoryObj<typeof meta>;

const basicCode = `<script setup>
import { ref } from 'vue';
const checked = ref(false);
</script>

<template>
    <Checkbox v-model="checked" label="Accept terms and conditions" />
</template>`;

export const Basic: Story = {
    render: () => ({
        components: { Checkbox },
        setup() {
            const checked = ref(false);
            return { checked };
        },
        template: `<Checkbox v-model="checked" label="Accept terms and conditions" />`,
    }),
    parameters: {
        docs: {
            source: { code: basicCode },
        },
    },
};

const withDescriptionCode = `<script setup>
import { ref } from 'vue';
const checked = ref(false);
</script>

<template>
    <Checkbox 
        v-model="checked" 
        label="Subscribe to newsletter"
        description="Receive weekly updates about new features and content"
    />
</template>`;

export const WithDescription: Story = {
    render: () => ({
        components: { Checkbox },
        setup() {
            const checked = ref(false);
            return { checked };
        },
        template: `<Checkbox 
            v-model="checked" 
            label="Subscribe to newsletter"
            description="Receive weekly updates about new features and content"
        />`,
    }),
    parameters: {
        docs: {
            source: { code: withDescriptionCode },
        },
    },
};

const sizesCode = `<script setup>
import { ref } from 'vue';
const checked = ref(false);
</script>

<template>
    <div class="space-y-3">
        <Checkbox v-model="checked" label="Small checkbox" size="sm" />
        <Checkbox v-model="checked" label="Base checkbox" size="base" />
    </div>
</template>`;

export const Sizes: Story = {
    render: () => ({
        components: { Checkbox },
        setup() {
            const checked = ref(false);
            return { checked };
        },
        template: `<div class="space-y-3">
            <Checkbox v-model="checked" label="Small checkbox" size="sm" />
            <Checkbox v-model="checked" label="Base checkbox" size="base" />
        </div>`,
    }),
    parameters: {
        docs: {
            source: { code: sizesCode },
        },
    },
};

const disabledCode = `<script setup>
import { ref } from 'vue';
const checked = ref(true);
</script>

<template>
    <Checkbox v-model="checked" label="Disabled checkbox" disabled />
</template>`;

export const Disabled: Story = {
    render: () => ({
        components: { Checkbox },
        setup() {
            const checked = ref(true);
            return { checked };
        },
        template: `<Checkbox v-model="checked" label="Disabled checkbox" disabled />`,
    }),
    parameters: {
        docs: {
            source: { code: disabledCode },
        },
    },
};

const soloCode = `<script setup>
import { ref } from 'vue';
const checked = ref(false);
</script>

<template>
    <Checkbox 
        v-model="checked" 
        label="Hidden label for accessibility"
        solo
    />
</template>`;

export const Solo: Story = {
    render: () => ({
        components: { Checkbox },
        setup() {
            const checked = ref(false);
            return { checked };
        },
        template: `<Checkbox 
            v-model="checked" 
            label="Hidden label for accessibility"
            solo
        />`,
    }),
    parameters: {
        docs: {
            source: { code: soloCode },
        },
    },
};

const groupCode = `<script setup>
import { ref } from 'vue';
const selected = ref(['html', 'css']);
</script>

<template>
    <CheckboxGroup v-model="selected">
        <Checkbox value="html" label="HTML" />
        <Checkbox value="css" label="CSS" />
        <Checkbox value="javascript" label="JavaScript" />
        <Checkbox value="vue" label="Vue.js" />
    </CheckboxGroup>
</template>`;

export const Group: Story = {
    render: () => ({
        components: { Checkbox, CheckboxGroup },
        setup() {
            const selected = ref(['html', 'css']);
            return { selected };
        },
        template: `<CheckboxGroup v-model="selected">
            <Checkbox value="html" label="HTML" />
            <Checkbox value="css" label="CSS" />
            <Checkbox value="javascript" label="JavaScript" />
            <Checkbox value="vue" label="Vue.js" />
        </CheckboxGroup>`,
    }),
    parameters: {
        docs: {
            source: { code: groupCode },
        },
    },
};

const inlineGroupCode = `<script setup>
import { ref } from 'vue';
const selected = ref(['red']);
</script>

<template>
    <CheckboxGroup v-model="selected" inline>
        <Checkbox value="red" label="Red" />
        <Checkbox value="green" label="Green" />
        <Checkbox value="blue" label="Blue" />
    </CheckboxGroup>
</template>`;

export const InlineGroup: Story = {
    render: () => ({
        components: { Checkbox, CheckboxGroup },
        setup() {
            const selected = ref(['red']);
            return { selected };
        },
        template: `<CheckboxGroup v-model="selected" inline>
            <Checkbox value="red" label="Red" />
            <Checkbox value="green" label="Green" />
            <Checkbox value="blue" label="Blue" />
        </CheckboxGroup>`,
    }),
    parameters: {
        docs: {
            source: { code: inlineGroupCode },
        },
    },
};
