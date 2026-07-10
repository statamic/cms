import type { Meta, StoryObj } from '@storybook/vue3';
import { Checkbox, CheckboxGroup } from '@ui';
import { ref } from 'vue';

const meta = {
    title: 'Forms/Checkbox',
    component: Checkbox,
    subcomponents: { CheckboxGroup },
    argTypes: {
        size: {
            control: 'select',
            options: ['sm', 'base'],
        },
        align: {
            control: 'select',
            options: ['start', 'center'],
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

const basicCode = `
<Checkbox v-model="checked" label="Accept terms and conditions" />
`;

export const _DocsIntro: Story = {
    tags: ['!dev'],
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

const withDescriptionCode = `
<Checkbox 
    v-model="checked" 
    label="Subscribe to newsletter"
    description="Receive weekly updates about new features and content"
/>
`;

export const WithDescription: Story = {
    tags: ['!dev'],
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

const sizesCode = `
<div class="space-y-3">
    <Checkbox v-model="checked" label="Small checkbox" size="sm" />
    <Checkbox v-model="checked" label="Base checkbox" size="base" />
</div>
`;

export const Sizes: Story = {
    tags: ['!dev'],
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

const disabledCode = `
<Checkbox v-model="checked" label="Disabled checkbox" disabled />
`;

export const Disabled: Story = {
    tags: ['!dev'],
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

const soloCode = `
<Checkbox 
    v-model="checked" 
    label="Hidden label for accessibility"
    solo
/>
`;

export const Solo: Story = {
    tags: ['!dev'],
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

const groupCode = `
<CheckboxGroup v-model="selected">
    <Checkbox value="html" label="HTML" />
    <Checkbox value="css" label="CSS" />
    <Checkbox value="javascript" label="JavaScript" />
    <Checkbox value="vue" label="Vue.js" />
</CheckboxGroup>
`;

export const Group: Story = {
    tags: ['!dev'],
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

const inlineGroupCode = `
<CheckboxGroup v-model="selected" inline>
    <Checkbox value="red" label="Red" />
    <Checkbox value="green" label="Green" />
    <Checkbox value="blue" label="Blue" />
</CheckboxGroup>
`;

export const InlineGroup: Story = {
    tags: ['!dev'],
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
