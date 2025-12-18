import type {Meta, StoryObj} from '@storybook/vue3';
import {
    Button,
    Context,
    ContextFooter,
    ContextHeader,
    ContextItem,
    ContextLabel,
    ContextMenu,
    ContextSeparator
} from '@ui';

const meta = {
    title: 'Components/Context',
    component: Context,
    subcomponents: {
        ContextMenu,
        ContextItem,
        ContextHeader,
        ContextFooter,
        ContextLabel,
        ContextSeparator,
    },
    argTypes: {
        align: {
            control: 'select',
            options: ['start', 'center', 'end'],
            description: 'The preferred alignment against the trigger. May change when collisions occur. <br><br> Options: `start`, `center`, `end`',
        },
        offset: {
            control: 'number',
            description: 'The distance in pixels from the trigger.',
        },
        side: {
            control: 'select',
            options: ['top', 'bottom', 'left', 'right'],
            description: 'The preferred side of the trigger to render against when open. <br><br> Options: `top`, `bottom`, `left`, `right`',
        },
    },
} satisfies Meta<typeof Context>;

export default meta;
type Story = StoryObj<typeof meta>;

const defaultCode = `
<Context>
    <template #trigger>
        <Button text="Right Click Me" />
    </template>
    <ContextMenu>
        <ContextItem text="Edit" />
        <ContextItem text="Duplicate" />
        <ContextItem text="Archive" />
        <ContextSeparator />
        <ContextItem text="Delete" variant="destructive" />
    </ContextMenu>
</Context>
`;

export const _DocsIntro: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: defaultCode }
        }
    },
    render: () => ({
        components: { Context, ContextMenu, ContextItem, ContextSeparator, Button },
        template: defaultCode,
    }),
};

const iconsCode = `
<Context>
    <template #trigger>
        <Button text="Actions" />
    </template>
    <ContextMenu>
        <ContextItem text="Edit" icon="pencil" />
        <ContextItem text="Duplicate" icon="duplicate" />
        <ContextItem text="Download" icon="arrow-down" />
        <ContextSeparator />
        <ContextItem text="Delete" icon="trash" variant="destructive" />
    </ContextMenu>
</Context>
`;

export const _Icons: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: iconsCode }
        }
    },
    render: () => ({
        components: { Context, ContextMenu, ContextItem, ContextSeparator, Button },
        template: iconsCode,
    }),
};

const withHeaderFooterCode = `
<Context>
    <template #trigger>
        <Button text="File Options" />
    </template>
    <ContextHeader text="document.pdf" icon="file" />
    <ContextMenu>
        <ContextItem text="Open" icon="arrow-right" />
        <ContextItem text="Rename" icon="pencil" />
        <ContextItem text="Move" icon="folder" />
        <ContextSeparator />
        <ContextItem text="Delete" icon="trash" variant="destructive" />
    </ContextMenu>
    <ContextFooter text="View in Finder" icon="folder" />
</Context>
`;

export const _WithHeaderFooter: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: withHeaderFooterCode }
        }
    },
    render: () => ({
        components: { Context, ContextMenu, ContextItem, ContextHeader, ContextFooter, ContextSeparator, Button },
        template: withHeaderFooterCode,
    }),
};

const withLabelsCode = `
<Context>
    <template #trigger>
        <Button text="Edit Text" />
    </template>
    <ContextMenu>
        <ContextLabel text="Format" />
        <ContextItem text="Bold" icon="text-bold" />
        <ContextItem text="Italic" icon="text-italic" />
        <ContextSeparator />
        <ContextLabel text="Alignment" />
        <ContextItem text="Align Left" icon="paragraph-align-left" />
        <ContextItem text="Align Center" icon="paragraph-align-center" />
        <ContextItem text="Align Right" icon="paragraph-align-right" />
    </ContextMenu>
</Context>
`;

export const _WithLabels: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: withLabelsCode }
        }
    },
    render: () => ({
        components: { Context, ContextMenu, ContextItem, ContextLabel, ContextSeparator, Button },
        template: withLabelsCode,
    }),
};

const withLinksCode = `
<Context>
    <template #trigger>
        <Button text="Quick Links" />
    </template>
    <ContextMenu>
        <ContextItem text="Documentation" icon="book-next-page" href="https://statamic.dev" target="_blank" />
        <ContextItem text="GitHub" icon="social-github-logo" href="https://github.com/statamic" target="_blank" />
        <ContextItem text="Discord" icon="social-discord-logo" href="https://statamic.com/discord" target="_blank" />
    </ContextMenu>
</Context>
`;

export const _WithLinks: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: withLinksCode }
        }
    },
    render: () => ({
        components: { Context, ContextMenu, ContextItem, Button },
        template: withLinksCode,
    }),
};

const disabledCode = `
<Context>
    <template #trigger>
        <Button text="Go To..." />
    </template>
    <ContextMenu>
        <ContextItem text="Collections" icon="collections" />
        <ContextItem text="Taxonomies" icon="taxonomies" disabled />
        <ContextItem text="Globals" icon="globals" />
    </ContextMenu>
</Context>
`;

export const _Disabled: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: disabledCode }
        }
    },
    render: () => ({
        components: { Context, ContextMenu, ContextItem, Button },
        template: disabledCode,
    }),
};
