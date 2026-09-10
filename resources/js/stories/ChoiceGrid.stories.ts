import type {Meta, StoryObj} from '@storybook/vue3';
import {ref} from 'vue';
import {expect, fn, userEvent, within} from 'storybook/test';
import {ChoiceGrid} from '@ui';

const meta = {
    title: 'Forms/ChoiceGrid',
    component: ChoiceGrid,
    argTypes: {
        gap: {
            control: 'select',
            options: ['sm', 'base', 'lg'],
        },
        'update:modelValue': {
            description: 'Event handler called when the selection changes.',
            table: {
                category: 'events',
                type: { summary: '(value: string | string[]) => void' }
            }
        }
    },
} satisfies Meta<typeof ChoiceGrid>;

export default meta;
type Story = StoryObj<typeof meta>;

// Inline, so the stories don't depend on the network when they run in CI.
const image = (hue: number) => `data:image/svg+xml,${encodeURIComponent(
    `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 480"><rect width="640" height="480" fill="hsl(${hue} 60% 70%)"/></svg>`
)}`;

const seasons = [
    { value: 'spring', label: 'Spring', image: image(110) },
    { value: 'summer', label: 'Summer', image: image(45) },
    { value: 'autumn', label: 'Autumn', image: image(20) },
    { value: 'winter', label: 'Winter', image: image(210) },
];

const defaultCode = `
<ChoiceGrid v-model="season" :options="options" :columns="2" label="Pick a season" />
`;

export const _DocsIntro: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: defaultCode }
        }
    },
    render: () => ({
        components: { ChoiceGrid },
        setup() {
            const season = ref('summer');
            return { season, options: seasons };
        },
        template: defaultCode,
    }),
};

const multipleCode = `
<ChoiceGrid v-model="chosen" :options="options" :columns="2" multiple label="Pick your seasons" />
`;

export const _Multiple: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: multipleCode }
        }
    },
    render: () => ({
        components: { ChoiceGrid },
        setup() {
            const chosen = ref(['spring', 'autumn']);
            return { chosen, options: seasons };
        },
        template: multipleCode,
    }),
};

const badgesCode = `
<ChoiceGrid v-model="answer" :options="options" :columns="3" label="Which layout?" />
`;

export const _Badges: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: badgesCode }
        }
    },
    render: () => ({
        components: { ChoiceGrid },
        setup() {
            const answer = ref(null);
            const options = seasons.slice(0, 3).map((option, index) => ({
                ...option,
                badge: String.fromCharCode(65 + index),
            }));
            return { answer, options };
        },
        template: badgesCode,
    }),
};

const aspectRatioCode = `
<ChoiceGrid v-model="season" :options="options" :columns="4" aspect-ratio="1/1" gap="lg" />
`;

export const _AspectRatio: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: aspectRatioCode }
        }
    },
    render: () => ({
        components: { ChoiceGrid },
        setup() {
            const season = ref('winter');
            return { season, options: seasons };
        },
        template: aspectRatioCode,
    }),
};

const placeholderCode = `
<ChoiceGrid v-model="answer" :options="options" :columns="2" />
`;

export const _WithoutImages: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: placeholderCode }
        }
    },
    render: () => ({
        components: { ChoiceGrid },
        setup() {
            const answer = ref(null);
            const options = [
                { value: 'one', label: 'Awaiting artwork' },
                { value: 'two', label: 'Also awaiting artwork' },
            ];
            return { answer, options };
        },
        template: placeholderCode,
    }),
};

export const TestSelectsSingleOption: Story = {
    tags: ['!dev', 'test'],
    args: {
        'onUpdate:modelValue': fn(),
    },
    render: (args) => ({
        components: { ChoiceGrid },
        setup() {
            return { options: seasons, onUpdate: args['onUpdate:modelValue'] };
        },
        template: `<ChoiceGrid :options="options" @update:modelValue="onUpdate" />`,
    }),
    play: async ({ canvasElement, args }) => {
        const canvas = within(canvasElement);

        await userEvent.click(canvas.getByText('Autumn'));

        expect(args['onUpdate:modelValue']).toHaveBeenCalledWith('autumn');
        expect(canvas.getAllByRole('radio')).toHaveLength(4);
    },
};

export const TestTogglesMultipleOptions: Story = {
    tags: ['!dev', 'test'],
    args: {
        'onUpdate:modelValue': fn(),
    },
    render: (args) => ({
        components: { ChoiceGrid },
        setup() {
            return { options: seasons, onUpdate: args['onUpdate:modelValue'] };
        },
        template: `<ChoiceGrid :model-value="['spring']" :options="options" multiple @update:modelValue="onUpdate" />`,
    }),
    play: async ({ canvasElement, args }) => {
        const canvas = within(canvasElement);

        await userEvent.click(canvas.getByText('Winter'));
        expect(args['onUpdate:modelValue']).toHaveBeenCalledWith(['spring', 'winter']);

        await userEvent.click(canvas.getByText('Spring'));
        expect(args['onUpdate:modelValue']).toHaveBeenCalledWith([]);
    },
};

export const TestDisabledOptionsCannotBeSelected: Story = {
    tags: ['!dev', 'test'],
    args: {
        'onUpdate:modelValue': fn(),
    },
    render: (args) => ({
        components: { ChoiceGrid },
        setup() {
            return { options: seasons, onUpdate: args['onUpdate:modelValue'] };
        },
        template: `<ChoiceGrid :options="options" disabled @update:modelValue="onUpdate" />`,
    }),
    play: async ({ canvasElement, args }) => {
        const canvas = within(canvasElement);

        await userEvent.click(canvas.getByText('Summer'));

        expect(args['onUpdate:modelValue']).not.toHaveBeenCalled();
    },
};
