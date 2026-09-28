import type {Meta, StoryObj} from '@storybook/vue3';
import {Button, ButtonGroup} from '@statamic/cms/ui';
import {icons} from './icons';

const meta = {
    title: 'Forms/Button',
    component: Button,
    argTypes: {
        icon: {
            control: 'select',
            options: icons,
        },
        iconAppend: {
            control: 'select',
            options: icons,
        },
        size: {
            control: 'select',
            options: ['2xs', 'xs', 'sm', 'base', 'lg'],
        },
        variant: {
            control: 'select',
            options: ['default', 'primary', 'danger', 'filled', 'ghost', 'ghost-pressed', 'subtle', 'pressed'],
        },
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
            return { args };
        },
        template: `
            <div class="flex flex-wrap gap-2">
                <Button v-bind="args" variant="default" text="Default" />
                <Button v-bind="args" variant="primary" text="Primary" />
                <Button v-bind="args" variant="danger" text="Danger" />
                <Button v-bind="args" variant="filled" text="Filled" />
                <Button v-bind="args" variant="ghost" text="Ghost" />
                <Button v-bind="args" variant="subtle" text="Subtle" />
                <Button v-bind="args" variant="pressed" text="Pressed" />
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
            return { args };
        },
        template: `
            <div class="flex flex-wrap gap-2 items-center">
                <Button v-bind="args" size="lg" text="Large" />
                <Button v-bind="args" size="base" text="Base" />
                <Button v-bind="args" size="sm" text="Small" />
                <Button v-bind="args" size="xs" text="Extra Small" />
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
                    <Button icon="arrow-left" text="Prepend" />
                    <Button icon-append="arrow-right" text="Append" />
                    <Button icon="arrow-left" icon-append="arrow-right" text="Both" />
                    <Button icon="cog" icon-only />
                `,
            },
        },
    },
    render: (args) => ({
        components: { Button },
        setup() {
            return { args };
        },
        template: `
            <div class="flex flex-wrap gap-2 items-center">
                <Button v-bind="args" icon="arrow-left" text="Prepend" />
                <Button v-bind="args" icon-append="arrow-right" text="Append" />
                <Button v-bind="args" icon="arrow-left" icon-append="arrow-right" text="Both" />
                <Button v-bind="args" icon="cog" icon-only />
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
            return { args };
        },
        template: `
            <div class="flex flex-wrap gap-2 items-center">
                <Button v-bind="args" round icon="plus" />
                <Button v-bind="args" round icon="plus" text="Add" />
                <Button v-bind="args" round text="Add" />
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
            return { args };
        },
        template: `
            <div class="flex flex-wrap items-center">
                <Button v-bind="args" inset variant="ghost" icon="x" />
                <Button v-bind="args" inset variant="ghost" icon="checkmark" />
            </div>
        `,
    }),
};

export const ButtonGroups: Story = {
    args: {
        text: 'Save & Continue',
    },
    parameters: {
        docs: {
            source: {
                code: `
                    <ButtonGroup>
                        <Button text="Apply" />
                        <Button icon="save" text="Save All" />
                    </ButtonGroup>
                `,
            },
        },
    },
    render: (args) => ({
        components: { ButtonGroup, Button },
        setup() {
            return { args };
        },
        template: `
            <ButtonGroup>
                <Button text="Apply" />
                <Button icon="save" text="Save All" />
            </ButtonGroup>
        `,
    }),
};

export const ButtonGroupOverflowStack: Story = {
    parameters: {
        docs: {
            source: {
                code: `
                    <ButtonGroup overflow="stack">
                        <Button text="Option A" />
                        <Button text="Option B" />
                        <Button text="Option C" />
                        <Button text="Option D" />
                        <Button text="Option E" />
                    </ButtonGroup>
                `,
            },
        },
    },
    render: () => ({
        components: { ButtonGroup, Button },
        template: `
            <div class="w-72">
                <ButtonGroup overflow="stack">
                    <Button text="Option A" />
                    <Button text="Option B" />
                    <Button text="Option C" />
                    <Button text="Option D" />
                    <Button text="Option E" />
                </ButtonGroup>
            </div>
        `,
    }),
};

export const ButtonGroupOverflowGap: Story = {
    parameters: {
        docs: {
            source: {
                code: `
                    <ButtonGroup overflow="gap">
                        <Button text="Option A" />
                        <Button text="Option B" />
                        <Button text="Option C" />
                        <Button text="Option D" />
                        <Button text="Option E" />
                    </ButtonGroup>
                `,
            },
        },
    },
    render: () => ({
        components: { ButtonGroup, Button },
        template: `
            <div class="w-72">
                <ButtonGroup overflow="gap">
                    <Button text="Option A" />
                    <Button text="Option B" />
                    <Button text="Option C" />
                    <Button text="Option D" />
                    <Button text="Option E" />
                </ButtonGroup>
            </div>
        `,
    }),
};

export const ButtonGroupOverflowVariations: Story = {
    render: () => ({
        components: { ButtonGroup, Button },
        template: `
            <div class="space-y-8">
                <div>
                    <p class="text-xs font-mono text-gray-500 mb-2">overflow="stack" — fits</p>
                    <ButtonGroup overflow="stack">
                        <Button text="Option A" />
                        <Button text="Option B" />
                    </ButtonGroup>
                </div>
                <div>
                    <p class="text-xs font-mono text-gray-500 mb-2">overflow="stack" — overflows</p>
                    <div class="w-48">
                        <ButtonGroup overflow="stack">
                            <Button text="Option A" />
                            <Button text="Option B" />
                            <Button text="Option C" />
                        </ButtonGroup>
                    </div>
                </div>
                <div>
                    <p class="text-xs font-mono text-gray-500 mb-2">overflow="gap" — fits</p>
                    <ButtonGroup overflow="gap">
                        <Button text="Option A" />
                        <Button text="Option B" />
                    </ButtonGroup>
                </div>
                <div>
                    <p class="text-xs font-mono text-gray-500 mb-2">overflow="gap" — overflows</p>
                    <div class="w-72">
                        <ButtonGroup overflow="gap">
                            <Button text="Option A" />
                            <Button text="Option B" />
                            <Button text="Option C" />
                            <Button text="Option D" />
                            <Button text="Option E" />
                            <Button text="Option F" />
                            <Button text="Option G" />
                        </ButtonGroup>
                    </div>
                </div>
            </div>
        `,
    }),
};