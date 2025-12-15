import type {Meta, StoryObj} from '@storybook/vue3';
import {Button, Stack, StackClose, StackTitle} from '@ui';

const meta = {
    title: 'Components/Stack',
    component: Stack,
    argTypes: {
        title: { control: 'text' },
        icon: { control: 'text' },
        blur: { control: 'boolean' },
        dismissible: { control: 'boolean' },
        open: { control: 'boolean' },
    },
} satisfies Meta<typeof Stack>;

export default meta;
type Story = StoryObj<typeof meta>;

const defaultCode = `
<Stack title="That's Pretty Neat">
    <template #trigger>
        <Button text="How neat is that?" />
    </template>
</Stack>
`;

export const _DocsIntro: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: defaultCode }
        }
    },
    render: () => ({
        components: { Stack, Button },
        template: defaultCode,
    }),
};

const customTitleCode = `
<Stack>
    <template #trigger>
        <Button text="How neat is that?" />
    </template>
    <StackTitle class="text-center flex items-center justify-between">
        <span>🍁</span>
        <h2 class="font-serif text-xl">What's why they call it neature!</h2>
        <span>🍁</span>
    </StackTitle>
</Stack>
`;

export const _CustomTitle: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: customTitleCode }
        }
    },
    render: () => ({
        components: { Stack, StackTitle, Button },
        template: customTitleCode,
    }),
};

const closeButtonCode = `
<Stack title="Hey look a close button" class="text-center">
    <template #trigger>
        <Button text="Open Says Me" />
    </template>
    <StackClose class="text-center">
        <Button text="Close Says Me" />
    </StackClose>
</Stack>
`;

export const _CloseButton: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: closeButtonCode }
        }
    },
    render: () => ({
        components: { Stack, StackClose, Button },
        template: closeButtonCode,
    }),
};

const iconCode = `
<Stack title="That's Pretty Neat" icon="fire-flame-burn-hot">
    <template #trigger>
        <Button text="How neat is that?" />
    </template>
</Stack>
`;

export const _WithIcon: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: iconCode }
        }
    },
    render: () => ({
        components: { Stack, Button },
        template: iconCode,
    }),
};