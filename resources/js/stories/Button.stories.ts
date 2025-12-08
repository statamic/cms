import type { Meta, StoryObj } from '@storybook/vue3';
import { Button } from '@ui';
import { computed } from 'vue';

const meta = {
    title: 'Components/Button',
    component: Button,
    argTypes: {
        variant: {
            control: 'select',
            options: ['default', 'primary', 'danger', 'filled', 'ghost', 'ghost-pressed', 'subtle', 'pressed'],
        },
        size: {
            control: 'select',
            options: ['lg', 'base', 'sm', 'xs'],
        },
        text: { control: 'text' },
        icon: { control: 'text' },
        iconAppend: { control: 'text' },
        loading: { control: 'boolean' },
        disabled: { control: 'boolean' },
        round: { control: 'boolean' },
        iconOnly: { control: 'boolean' },
    },
} satisfies Meta<typeof Button>;

export default meta;
type Story = StoryObj<typeof meta>;

export const Default: Story = {
    args: {
        text: 'Click me',
    },
};

export const _DocsIntro: Story = {
    tags: ['!dev'],
    args: {
        text: 'Click me!',
        variant: 'primary',
        icon: 'ai-spark'
    }
};

export const Variants: Story = {
    argTypes: {
        variant: { control: { disable: true } },
        text: { control: { disable: true } },
    },
    parameters: {
        docs: {
            source: {
                code: `
                    <Button variant="default" text="Default" />
                    <Button variant="primary" text="Primary" />
                    <Button variant="danger" text="Danger" />
                    <Button variant="filled" text="Filled" />
                    <Button variant="ghost" text="Ghost" />
                    <Button variant="subtle" text="Subtle" />
                    <Button variant="pressed" text="Pressed" />
                `,
            },
        },
    },
    render: (args) => ({
        components: { Button },
        setup() {
            const sharedProps = computed(() => {
                const { variant, text, ...rest } = args;
                return rest;
            });
            return { sharedProps };
        },
        template: `
            <div class="flex flex-wrap gap-2">
                <Button variant="default" text="Default" v-bind="sharedProps" />
                <Button variant="primary" text="Primary" v-bind="sharedProps" />
                <Button variant="danger" text="Danger" v-bind="sharedProps" />
                <Button variant="filled" text="Filled" v-bind="sharedProps" />
                <Button variant="ghost" text="Ghost" v-bind="sharedProps" />
                <Button variant="subtle" text="Subtle" v-bind="sharedProps" />
                <Button variant="pressed" text="Pressed" v-bind="sharedProps" />
            </div>
        `,
    }),
};

export const Sizes: Story = {
    argTypes: {
        size: { control: { disable: true } },
        text: { control: { disable: true } },
    },
    parameters: {
        docs: {
            source: {
                code: `
                    <Button size="lg" text="Large" />
                    <Button size="base" text="Base" />
                    <Button size="sm" text="Small" />
                    <Button size="xs" text="Extra Small" />
                `,
            },
        },
    },
    render: (args) => ({
        components: { Button },
        setup() {
            const sharedProps = computed(() => {
                const { size, text, ...rest } = args;
                return rest;
            });
            return { sharedProps };
        },
        template: `
            <div class="flex flex-wrap gap-2 items-center">
                <Button size="lg" text="Large" v-bind="sharedProps" />
                <Button size="base" text="Base" v-bind="sharedProps" />
                <Button size="sm" text="Small" v-bind="sharedProps" />
                <Button size="xs" text="Extra Small" v-bind="sharedProps" />
            </div>
        `,
    }),
};

export const Icons: Story = {
    argTypes: {
        icon: { control: { disable: true } },
        iconAppend: { control: { disable: true } },
        iconOnly: { control: { disable: true } },
        text: { control: { disable: true } },
    },
    parameters: {
        docs: {
            source: {
                code: `
                    <Button size="lg" text="Large" />
                    <Button size="base" text="Base" />
                    <Button size="sm" text="Small" />
                    <Button size="xs" text="Extra Small" />
                `,
            },
        },
    },
    render: (args) => ({
        components: { Button },
        setup() {
            const sharedProps = computed(() => {
                const { size, text, ...rest } = args;
                return rest;
            });
            return { sharedProps };
        },
        template: `
            <div class="flex flex-wrap gap-2 items-center">
                <Button icon="arrow-left" text="Prepend" v-bind="sharedProps" />
                <Button icon-append="arrow-right" text="Append" v-bind="sharedProps" />
                <Button icon="arrow-left" icon-append="arrow-right" text="Both" v-bind="sharedProps" />
                <Button icon="cog" icon-only v-bind="sharedProps" />
            </div>
        `,
    }),
};

export const Round: Story = {
    argTypes: {
        round: { control: { disable: true } },
        iconOnly: { control: { disable: true } },
        text: { control: { disable: true } },
    },
    parameters: {
        docs: {
            source: {
                code: `
                    <Button round icon="plus" />
                    <Button round icon="plus" text="Add" />
                    <Button round text="Add" />
                `,
            },
        },
    },
    render: (args) => ({
        components: { Button },
        setup() {
            const sharedProps = computed(() => {
                const { round, ...rest } = args;
                return rest;
            });
            return { sharedProps };
        },
        template: `
            <div class="flex flex-wrap gap-2 items-center">
                <Button round icon="plus" v-bind="sharedProps" />
                <Button round icon="plus" text="Add" v-bind="sharedProps" />
                <Button round text="Add" v-bind="sharedProps" />
            </div>
        `,
    }),
};

export const Loading: Story = {
    args: {
        text: 'Loading',
        loading: true,
    },
};

export const FullWidth: Story = {
    args: {
        text: 'Save & Continue',
    },
    parameters: {
        docs: {
            source: {
                code: `
                    <Button text="Save & Continue" class="w-full" />
                `,
            },
        },
    },
    render: (args) => ({
        components: { Button },
        setup() {
            return { args };
        },
        template: `<div class="w-96"><Button v-bind="args" class="w-full" /></div>`,
    }),
};

export const Link: Story = {
    args: {
        text: 'Visit Statamic.com',
        iconAppend: 'arrow-up-right',
        href: 'https://statamic.com',
        target: '_blank',
    },
};

export const Inset: Story = {
    argTypes: {
        variant: { control: { disable: true } },
        inset: { control: { disable: true } },
        text: { control: { disable: true } },
        icon: { control: { disable: true } },
    },
    parameters: {
        docs: {
            source: {
                code: `
                    <Button inset variant="ghost" icon="x" />
                    <Button inset variant="ghost" icon="checkmark" />
                `,
            },
        },
    },
    render: (args) => ({
        components: { Button },
        setup() {
            const sharedProps = computed(() => {
                const { inset, variant, icon, ...rest } = args;
                return rest;
            });
            return { sharedProps };
        },
        template: `
            <div class="flex flex-wrap items-center">
                <Button inset variant="ghost" icon="x" v-bind="sharedProps" />
                <Button inset variant="ghost" icon="checkmark" v-bind="sharedProps" />
            </div>
        `,
    }),
};
