import type {Meta, StoryObj} from '@storybook/vue3';
import {ref} from 'vue';
import {expect, fn, userEvent, within} from 'storybook/test';
import {Field, RankList} from '@ui';

const meta = {
    title: 'Forms/RankList',
    component: RankList,
    argTypes: {
        'update:modelValue': {
            description: 'Event handler called when the order changes.',
            table: {
                category: 'events',
                type: { summary: '(value: string[]) => void' }
            }
        }
    },
} satisfies Meta<typeof RankList>;

export default meta;
type Story = StoryObj<typeof meta>;

const seasons = [
    { value: 'spring', label: 'Spring' },
    { value: 'summer', label: 'Summer' },
    { value: 'autumn', label: 'Autumn' },
    { value: 'winter', label: 'Winter' },
];

const defaultCode = `
<RankList v-model="order" :options="options" />
`;

export const _DocsIntro: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: defaultCode }
        }
    },
    render: () => ({
        components: { RankList },
        setup() {
            const order = ref([]);
            return { order, options: seasons };
        },
        template: defaultCode,
    }),
};

const orderedCode = `
<Field label="Rank your favourite seasons">
    <RankList v-model="order" :options="options" />
</Field>
`;

export const _ExistingOrder: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: orderedCode }
        }
    },
    render: () => ({
        components: { Field, RankList },
        setup() {
            const order = ref(['summer', 'spring', 'winter', 'autumn']);
            return { order, options: seasons };
        },
        template: orderedCode,
    }),
};

const disabledCode = `
<RankList :model-value="order" :options="options" disabled />
`;

export const _Disabled: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: disabledCode }
        }
    },
    render: () => ({
        components: { RankList },
        setup() {
            return { order: ['winter', 'autumn', 'summer', 'spring'], options: seasons };
        },
        template: disabledCode,
    }),
};

export const TestFallsBackToOptionOrder: Story = {
    tags: ['!dev', 'test'],
    render: () => ({
        components: { RankList },
        setup() {
            return { options: seasons };
        },
        template: `<RankList :options="options" />`,
    }),
    play: async ({ canvasElement }) => {
        const items = within(canvasElement).getAllByRole('listitem');

        expect(items.map((item) => item.textContent?.trim())).toEqual(['Spring', 'Summer', 'Autumn', 'Winter']);
    },
};

export const TestTypingARankReorders: Story = {
    tags: ['!dev', 'test'],
    args: {
        'onUpdate:modelValue': fn(),
    },
    render: (args) => ({
        components: { RankList },
        setup() {
            return { options: seasons, onUpdate: args['onUpdate:modelValue'] };
        },
        template: `<RankList :options="options" @update:modelValue="onUpdate" />`,
    }),
    play: async ({ canvasElement, args }) => {
        const input = within(canvasElement).getByLabelText('Rank Winter') as HTMLInputElement;

        await userEvent.clear(input);
        await userEvent.type(input, '1');
        await userEvent.tab();

        expect(args['onUpdate:modelValue']).toHaveBeenCalledWith(['winter', 'spring', 'summer', 'autumn']);
    },
};

export const TestOutOfRangeRankIsClamped: Story = {
    tags: ['!dev', 'test'],
    args: {
        'onUpdate:modelValue': fn(),
    },
    render: (args) => ({
        components: { RankList },
        setup() {
            return { options: seasons, onUpdate: args['onUpdate:modelValue'] };
        },
        template: `<RankList :options="options" @update:modelValue="onUpdate" />`,
    }),
    play: async ({ canvasElement, args }) => {
        const input = within(canvasElement).getByLabelText('Rank Spring') as HTMLInputElement;

        await userEvent.clear(input);
        await userEvent.type(input, '9');
        await userEvent.tab();

        expect(args['onUpdate:modelValue']).toHaveBeenCalledWith(['summer', 'autumn', 'winter', 'spring']);
        expect(input.value).toBe('4');
    },
};
