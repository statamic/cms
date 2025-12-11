import type { Meta, StoryObj } from '@storybook/vue3';
import { Badge } from '@ui';
import { computed } from 'vue';

const meta = {
    title: 'Components/Badge',
    component: Badge,
    argTypes: {
        color: {
            control: 'select',
            options: ['default', 'amber', 'black', 'blue', 'cyan', 'emerald', 'fuchsia', 'green', 'indigo', 'lime', 'orange', 'pink', 'purple', 'red', 'rose', 'sky', 'teal', 'violet', 'white', 'yellow'],
        },
        size: {
            control: 'select',
            options: ['sm', 'default', 'lg'],
        },
        text: { control: 'text' },
        icon: { control: 'text' },
        iconAppend: { control: 'text' },
        prepend: { control: 'text' },
        append: { control: 'text' },
        pill: { control: 'boolean' },
    },
} satisfies Meta<typeof Badge>;

export default meta;
type Story = StoryObj<typeof meta>;

export const Default: Story = {
    args: {
        text: 'Badge',
    },
};

export const _DocsIntro: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: {
                code: `
                    <Badge color="green" text="New" size="lg" />
                    <Badge color="red" text="Hot" size="lg" />
                    <Badge color="amber" text="Soup" size="lg" />
                `
            }
        }
    },
    render: (args) => ({
        components: { Badge },
        template: `
            <div class="flex flex-wrap gap-2">
                <Badge color="green" text="New" size="lg" />
                <Badge color="red" text="Hot" size="lg" />
                <Badge color="amber" text="Soup" size="lg" />
            </div>
        `
    })
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
                    <Badge size="sm" text="Small" />
                    <Badge size="default" text="Default" />
                    <Badge size="lg" text="Large" />
                `,
            },
        },
    },
    render: (args) => ({
        components: { Badge },
        setup() {
            const sharedProps = computed(() => {
                const { size, text, ...rest } = args;
                return rest;
            });
            return { sharedProps };
        },
        template: `
            <div class="flex flex-wrap gap-2 items-center">
                <Badge size="sm" text="Small" v-bind="sharedProps" />
                <Badge size="default" text="Default" v-bind="sharedProps" />
                <Badge size="lg" text="Large" v-bind="sharedProps" />
            </div>
        `,
    }),
};

export const Colors: Story = {
    argTypes: {
        color: { control: { disable: true } },
        text: { control: { disable: true } },
    },
    parameters: {
        docs: {
            source: {
                code: `
                    <Badge color="default" text="Default" />
                    <Badge color="blue" text="Blue" />
                    <Badge color="green" text="Green" />
                    <Badge color="red" text="Red" />
                    <Badge color="yellow" text="Yellow" />
                    <Badge color="purple" text="Purple" />
                    <Badge color="pink" text="Pink" />
                    <Badge color="indigo" text="Indigo" />
                    <Badge color="cyan" text="Cyan" />
                    <Badge color="teal" text="Teal" />
                    <Badge color="orange" text="Orange" />
                    <Badge color="amber" text="Amber" />
                    <Badge color="lime" text="Lime" />
                    <Badge color="emerald" text="Emerald" />
                    <Badge color="sky" text="Sky" />
                    <Badge color="violet" text="Violet" />
                    <Badge color="fuchsia" text="Fuchsia" />
                    <Badge color="rose" text="Rose" />
                    <Badge color="black" text="Black" />
                    <Badge color="white" text="White" />
                `,
            },
        },
    },
    render: (args) => ({
        components: { Badge },
        setup() {
            const sharedProps = computed(() => {
                const { color, text, ...rest } = args;
                return rest;
            });
            return { sharedProps };
        },
        template: `
            <div class="flex flex-wrap gap-2">
                <Badge color="default" text="Default" v-bind="sharedProps" />
                <Badge color="blue" text="Blue" v-bind="sharedProps" />
                <Badge color="green" text="Green" v-bind="sharedProps" />
                <Badge color="red" text="Red" v-bind="sharedProps" />
                <Badge color="yellow" text="Yellow" v-bind="sharedProps" />
                <Badge color="purple" text="Purple" v-bind="sharedProps" />
                <Badge color="pink" text="Pink" v-bind="sharedProps" />
                <Badge color="indigo" text="Indigo" v-bind="sharedProps" />
                <Badge color="cyan" text="Cyan" v-bind="sharedProps" />
                <Badge color="teal" text="Teal" v-bind="sharedProps" />
                <Badge color="orange" text="Orange" v-bind="sharedProps" />
                <Badge color="amber" text="Amber" v-bind="sharedProps" />
                <Badge color="lime" text="Lime" v-bind="sharedProps" />
                <Badge color="emerald" text="Emerald" v-bind="sharedProps" />
                <Badge color="sky" text="Sky" v-bind="sharedProps" />
                <Badge color="violet" text="Violet" v-bind="sharedProps" />
                <Badge color="fuchsia" text="Fuchsia" v-bind="sharedProps" />
                <Badge color="rose" text="Rose" v-bind="sharedProps" />
                <Badge color="black" text="Black" v-bind="sharedProps" />
                <Badge color="white" text="White" v-bind="sharedProps" />
            </div>
        `,
    }),
};

const appendPrependDocCode = `
    <Badge text="Events" prepend="42" color="black" />
    <Badge text="Updates" append="31" color="purple" />
`;
export const _AppendPrependDocs: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: appendPrependDocCode }
        }
    },
    render: (args) => ({
        components: { Badge },
        template: `
            <div class="flex flex-wrap gap-2 items-center">
                ${appendPrependDocCode}
            </div>
        `,
    }),
};

export const Append: Story = {
    argTypes: {
        prepend: { control: { disable: true } },
        append: { control: { disable: true } },
        text: { control: { disable: true } },
    },
    render: (args) => ({
        components: { Badge },
        setup() {
            const sharedProps = computed(() => {
                const { prepend, append, text, ...rest } = args;
                return rest;
            });
            return { sharedProps };
        },
        template: `
            <div class="flex flex-wrap gap-2 items-center">
                <Badge text="Events" append="42" v-bind="sharedProps" />
                <Badge text="Updates" prepend="31" v-bind="sharedProps" />
                <Badge text="Both" append="42" prepend="31" v-bind="sharedProps" />
            </div>
        `,
    }),
};

const iconsDocsCode = `
    <Badge icon="mail" text="david@hasselhoff.com" />
    <Badge icon-append="x" color="red" text="Delete" as="button" />
`;
export const _IconDocs: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: iconsDocsCode }
        }
    },
    render: (args) => ({
        components: { Badge },
        template: `
            <div class="flex flex-wrap gap-2 items-center">
                ${iconsDocsCode}
            </div>
        `,
    }),
};

export const Icons: Story = {
    argTypes: {
        icon: { control: { disable: true } },
        iconAppend: { control: { disable: true } },
        text: { control: { disable: true } },
    },
    render: (args) => ({
        components: { Badge },
        setup() {
            const sharedProps = computed(() => {
                const { icon, iconAppend, text, ...rest } = args;
                return rest;
            });
            return { sharedProps };
        },
        template: `
            <div class="flex flex-wrap gap-2 items-center">
                <Badge icon="mail" text="david@hassellhoff.com" v-bind="sharedProps" />
                <Badge icon-append="x" text="Delete" v-bind="sharedProps" />
                <Badge icon="mail" icon-append="x" text="Both" v-bind="sharedProps" />
            </div>
        `,
    }),
};

export const Pill: Story = {
    args: {
        text: 'Pill Badge',
        pill: true,
    },
};

export const Link: Story = {
    args: {
        text: 'Visit Statamic.com',
        href: 'https://statamic.com',
        target: '_blank',
        color: 'blue',
    },
};
