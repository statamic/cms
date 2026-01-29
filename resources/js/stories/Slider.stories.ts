import type {Meta, StoryObj} from '@storybook/vue3';
import {ref} from 'vue';
import {Field, Slider} from '@ui';

const meta = {
    title: 'Forms/Slider',
    component: Slider,
    argTypes: {
        size: {
            control: 'select',
            options: ['sm', 'base'],
        },
        variant: {
            control: 'select',
            options: ['default'],
        },
        'update:modelValue': {
            description: 'Event handler called when the slider value changes.',
            table: {
                category: 'events',
                type: { summary: '(value: number) => void' }
            }
        }
    },
} satisfies Meta<typeof Slider>;

export default meta;
type Story = StoryObj<typeof meta>;

const defaultCode = `
<Slider v-model="value" />
`;

export const _DocsIntro: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: defaultCode }
        }
    },
    render: () => ({
        components: { Slider },
        setup() {
            const value = ref(50);
            return { value };
        },
        template: defaultCode,
    }),
};

const rangeCode = `
<div class="space-y-6">
    <Field label="Opacity">
        <Slider v-model="opacity" :min="0" :max="1" :step="0.1" />
    </Field>
    <Field label="Items per page">
        <Slider v-model="perPage" :min="10" :max="100" :step="10" />
    </Field>
</div>
`;

export const _CustomRange: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: rangeCode }
        }
    },
    render: () => ({
        components: { Field, Slider },
        setup() {
            const opacity = ref(0.8);
            const perPage = ref(20);
            return { opacity, perPage };
        },
        template: rangeCode,
    }),
};

const sizesCode = `
<div class="space-y-6">
    <Field label="Base size">
        <Slider v-model="baseValue" />
    </Field>
    <Field label="Small size">
        <Slider v-model="smallValue" size="sm" />
    </Field>
</div>
`;

export const _Sizes: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: sizesCode }
        }
    },
    render: () => ({
        components: { Field, Slider },
        setup() {
            const baseValue = ref(50);
            const smallValue = ref(50);
            return { baseValue, smallValue };
        },
        template: sizesCode,
    }),
};
