import type {Meta, StoryObj} from '@storybook/vue3';
import {Badge} from '@statamic/cms/ui';
import {icons} from "@/stories/icons";

const meta = {
    title: 'Components/Badge',
    component: Badge,
    argTypes: {
        color: {
            control: 'select',
            options: ['default', 'amber', 'black', 'blue', 'cyan', 'emerald', 'fuchsia', 'green', 'indigo', 'lime', 'orange', 'pink', 'purple', 'red', 'rose', 'sky', 'teal', 'violet', 'white', 'yellow'],
        },
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
            options: ['sm', 'default', 'lg'],
        },
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
            return { args };
        },
        template: `
            <div class="flex flex-wrap gap-2 items-center">
                <Badge v-bind="args" size="sm" text="Small" />
                <Badge v-bind="args" size="default" text="Default" />
                <Badge v-bind="args" size="lg" text="Large" />
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
            return { args };
        },
        template: `
            <div class="flex flex-wrap gap-2">
                <Badge v-bind="args" color="default" text="Default" />
                <Badge v-bind="args" color="blue" text="Blue" />
                <Badge v-bind="args" color="green" text="Green" />
                <Badge v-bind="args" color="red" text="Red" />
                <Badge v-bind="args" color="yellow" text="Yellow" />
                <Badge v-bind="args" color="purple" text="Purple" />
                <Badge v-bind="args" color="pink" text="Pink" />
                <Badge v-bind="args" color="indigo" text="Indigo" />
                <Badge v-bind="args" color="cyan" text="Cyan" />
                <Badge v-bind="args" color="teal" text="Teal" />
                <Badge v-bind="args" color="orange" text="Orange" />
                <Badge v-bind="args" color="amber" text="Amber" />
                <Badge v-bind="args" color="lime" text="Lime" />
                <Badge v-bind="args" color="emerald" text="Emerald" />
                <Badge v-bind="args" color="sky" text="Sky" />
                <Badge v-bind="args" color="violet" text="Violet" />
                <Badge v-bind="args" color="fuchsia" text="Fuchsia" />
                <Badge v-bind="args" color="rose" text="Rose" />
                <Badge v-bind="args" color="black" text="Black" />
                <Badge v-bind="args" color="white" text="White" />
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
                <Badge text="Events" prepend="42" color="black" />
                <Badge text="Updates" append="31" color="purple" />
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
            return { args };
        },
        template: `
            <div class="flex flex-wrap gap-2 items-center">
                <Badge v-bind="args" text="Events" append="42" />
                <Badge v-bind="args" text="Updates" prepend="31" />
                <Badge v-bind="args" text="Both" append="42" prepend="31" />
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
                <Badge icon="mail" text="david@hasselhoff.com" />
                <Badge icon-append="x" color="red" text="Delete" as="button" />
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
            return { args };
        },
        template: `
            <div class="flex flex-wrap gap-2 items-center">
                <Badge v-bind="args" icon="mail" text="david@hassellhoff.com" />
                <Badge v-bind="args" icon-append="x" text="Delete" />
                <Badge v-bind="args" icon="mail" icon-append="x" text="Both" />
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
