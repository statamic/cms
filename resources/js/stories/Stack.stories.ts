import type {Meta, StoryObj} from '@storybook/vue3';
import {Button, Stack, StackClose, StackTitle} from '@ui';

const meta = {
    title: 'Overlays/Stack',
    component: Stack,
    subcomponents: {
        StackTitle,
        StackClose,
    },
    argTypes: {
        'opened': {
            description: 'Event handler called when the stack is opened.',
            table: {
                category: 'events',
                type: { summary: '() => void' },
            },
        },
        'update:open': {
            description: 'Event handler called when the open state of the stack changes.',
            table: {
                category: 'events',
                type: { summary: '(value: boolean) => void' },
            },
        },
    },
} satisfies Meta<typeof Stack>;

export default meta;
type Story = StoryObj<typeof meta>;

const defaultCode = `
<Stack v-model:open="isOpen" title="That's Pretty Neat">
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
        data: () => {
            return { isOpen: false };
        },
        template: defaultCode,
    }),
};

const customTitleCode = `
<Stack v-model:open="isOpen">
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
        data: () => {
            return { isOpen: false };
        },
        template: customTitleCode,
    }),
};

const closeButtonCode = `
<Stack 
    v-model:open="isOpen" 
    title="Hey look a close button" 
    class="text-center"
>
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
        data: () => {
            return { isOpen: false };
        },
        template: closeButtonCode,
    }),
};

const iconCode = `
<Stack 
    v-model:open="isOpen" 
    title="That's Pretty Neat" 
    icon="fire-flame-burn-hot"
>
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
        data: () => {
            return { isOpen: false };
        },
        template: iconCode,
    }),
};

const beforeCloseCode = `
<Stack 
    v-model:open="isOpen"
    :before-close="() => {
        if (confirm('Are you sure?')) {
            return true; // let it close
        } else {
            return false; // prevent it from closing
        }
    }"
>
    <!-- -->
</Stack>
`;

export const _BeforeClose: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: beforeCloseCode }
        }
    },
    render: () => ({
        components: { Stack, Button },
        data: () => {
            return { isOpen: false };
        },
        template: beforeCloseCode,
    }),
};