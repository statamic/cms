import type {Meta, StoryObj} from '@storybook/vue3';
import {ref} from 'vue';
import {expect, fn, userEvent, within} from 'storybook/test';
import {Field, StarRating} from '@ui';

const meta = {
    title: 'Forms/StarRating',
    component: StarRating,
    argTypes: {
        size: {
            control: 'select',
            options: ['sm', 'base', 'lg'],
        },
        'update:modelValue': {
            description: 'Event handler called when the rating changes.',
            table: {
                category: 'events',
                type: { summary: '(value: number) => void' }
            }
        }
    },
} satisfies Meta<typeof StarRating>;

export default meta;
type Story = StoryObj<typeof meta>;

const defaultCode = `
<StarRating v-model="rating" label="Rate your experience" />
`;

export const _DocsIntro: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: defaultCode }
        }
    },
    render: () => ({
        components: { StarRating },
        setup() {
            const rating = ref(null);
            return { rating };
        },
        template: defaultCode,
    }),
};

const halfStarsCode = `
<div class="space-y-6">
    <Field label="Whole stars">
        <StarRating v-model="whole" />
    </Field>
    <Field label="Half stars">
        <StarRating v-model="half" :step="0.5" />
    </Field>
</div>
`;

export const _HalfStars: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: halfStarsCode }
        }
    },
    render: () => ({
        components: { Field, StarRating },
        setup() {
            const whole = ref(3);
            const half = ref(3.5);
            return { whole, half };
        },
        template: halfStarsCode,
    }),
};

const maxCode = `
<div class="space-y-6">
    <Field label="Out of three">
        <StarRating v-model="three" :max="3" />
    </Field>
    <Field label="Out of ten">
        <StarRating v-model="ten" :max="10" />
    </Field>
</div>
`;

export const _Max: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: maxCode }
        }
    },
    render: () => ({
        components: { Field, StarRating },
        setup() {
            const three = ref(2);
            const ten = ref(7);
            return { three, ten };
        },
        template: maxCode,
    }),
};

const sizesCode = `
<div class="space-y-6">
    <Field label="Small"><StarRating v-model="small" size="sm" /></Field>
    <Field label="Base"><StarRating v-model="base" /></Field>
    <Field label="Large"><StarRating v-model="large" size="lg" /></Field>
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
        components: { Field, StarRating },
        setup() {
            const small = ref(4);
            const base = ref(4);
            const large = ref(4);
            return { small, base, large };
        },
        template: sizesCode,
    }),
};

const disabledCode = `
<StarRating :model-value="4" disabled />
`;

export const _Disabled: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: disabledCode }
        }
    },
    render: () => ({
        components: { StarRating },
        template: disabledCode,
    }),
};

export const TestStartsUnrated: Story = {
    tags: ['!dev', 'test'],
    render: () => ({
        components: { StarRating },
        template: `<StarRating label="Rating" />`,
    }),
    play: async ({ canvasElement }) => {
        const input = within(canvasElement).getByRole('slider') as HTMLInputElement;

        // The thumb sits at the minimum so it lines up with the first star, but nothing is filled in yet.
        expect(input).toHaveAttribute('data-unrated');
        expect(input.value).toBe('1');
    },
};

export const TestArrowKeySelectsMinimum: Story = {
    tags: ['!dev', 'test'],
    args: {
        'onUpdate:modelValue': fn(),
    },
    render: (args) => ({
        components: { StarRating },
        setup() {
            return { onUpdate: args['onUpdate:modelValue'] };
        },
        template: `<StarRating label="Rating" @update:modelValue="onUpdate" />`,
    }),
    play: async ({ canvasElement, args }) => {
        const input = within(canvasElement).getByRole('slider') as HTMLInputElement;

        input.focus();
        await userEvent.keyboard('{ArrowRight}');

        // Without intervention the browser would jump straight to two stars.
        expect(args['onUpdate:modelValue']).toHaveBeenCalledWith(1);
        expect(input).not.toHaveAttribute('data-unrated');
    },
};

export const TestPointerRevealsTheFill: Story = {
    tags: ['!dev', 'test'],
    render: () => ({
        components: { StarRating },
        template: `<StarRating label="Rating" />`,
    }),
    play: async ({ canvasElement }) => {
        const input = within(canvasElement).getByRole('slider') as HTMLInputElement;

        await userEvent.click(input);

        expect(input).not.toHaveAttribute('data-unrated');
    },
};

export const TestHalfStars: Story = {
    tags: ['!dev', 'test'],
    render: () => ({
        components: { StarRating },
        template: `<StarRating label="Rating" :model-value="3.5" :max="10" :step="0.5" />`,
    }),
    play: async ({ canvasElement }) => {
        const input = within(canvasElement).getByRole('slider') as HTMLInputElement;

        // The minimum falls back to a single step, so half stars can start at 0.5.
        expect(input.min).toBe('0.5');
        expect(input.max).toBe('10');
        expect(input.step).toBe('0.5');
        expect(input.value).toBe('3.5');
        expect(input).not.toHaveAttribute('data-unrated');
    },
};
