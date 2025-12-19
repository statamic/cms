import type {Meta, StoryObj} from '@storybook/vue3';
import {Button, Field, Input, Modal, ModalClose, ModalTitle} from '@ui';
import PortalTargets from "@/components/portals/PortalTargets.vue";

const meta = {
    title: 'Overlays/Modal',
    component: Modal,
    subcomponents: {
        ModalTitle,
        ModalClose,
    },
    argTypes: {
        'dismissed': {
            description: 'Event handler called when the modal is dismissed.',
            table: {
                category: 'events',
                type: { summary: '() => void' },
            },
        },
        'update:open': {
            description: 'Event handler called when the open state of the modal changes.',
            table: {
                category: 'events',
                type: { summary: '(value: boolean) => void' },
            },
        },
    },
} satisfies Meta<typeof Modal>;

export default meta;
type Story = StoryObj<typeof meta>;

const defaultCode = `
<Modal v-model:open="isOpen" title="That's Pretty Neat">
    <template #trigger>
        <Button text="How neat is that?" />
    </template>
</Modal>
`;

export const _DocsIntro: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: defaultCode }
        }
    },
    render: () => ({
        components: { Modal, Button, PortalTargets },
        data: () => {
            return { isOpen: false };
        },
        template: `<div>${defaultCode}</div><PortalTargets />`,
    }),
};

const customTitleCode = `
<Modal v-model:open="isOpen">
    <template #trigger>
        <Button text="How neat is that?" />
    </template>
    <ModalTitle class="text-center flex items-center justify-between">
        <span>🍁</span>
        <h2 class="font-serif text-xl">What's why they call it neature!</h2>
        <span>🍁</span>
    </ModalTitle>
</Modal>
`;

export const _CustomTitle: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: customTitleCode }
        }
    },
    render: () => ({
        components: { Modal, ModalTitle, Button },
        data: () => {
            return { isOpen: false };
        },
        template: customTitleCode,
    }),
};

const closeButtonCode = `
<Modal v-model:open="isOpen" title="Hey look a close button" class="text-center">
    <template #trigger>
        <Button text="Open Says Me" />
    </template>
    <ModalClose class="text-center">
        <Button text="Close Says Me" />
    </ModalClose>
</Modal>
`;

export const _CloseButton: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: closeButtonCode }
        }
    },
    render: () => ({
        components: { Modal, ModalClose, Button },
        data: () => {
            return { isOpen: false };
        },
        template: closeButtonCode,
    }),
};

const iconCode = `
<Modal v-model:open="isOpen" title="That's Pretty Neat" icon="fire-flame-burn-hot">
    <template #trigger>
        <Button text="How neat is that?" />
    </template>
</Modal>
`;

export const _WithIcon: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: iconCode }
        }
    },
    render: () => ({
        components: { Modal, Button },
        data: () => {
            return { isOpen: false };
        },
        template: iconCode,
    }),
};

const footerCode = `
<Modal v-model:open="isOpen" title="Create new user">
    <template #trigger>
        <Button text="Create User" variant="primary" />
    </template>
    <div class="space-y-6 py-3">
        <div class="flex gap-6">
            <Field label="First name" badge="Optional">
                <Input name="first_name" />
            </Field>
            <Field label="Last name" badge="Optional">
                <Input name="last_name" />
            </Field>
        </div>
        <Field label="Email">
            <Input name="email" type="email" />
        </Field>
        <Field label="Password">
            <Input label="Password" type="password" />
        </Field>
    </div>
    <template #footer>
        <div class="flex items-center justify-end space-x-3 pt-3 pb-1">
            <ModalClose>
                <Button text="Cancel" variant="ghost" />
            </ModalClose>
            <Button text="Create User" variant="primary" />
        </div>
    </template>
</Modal>
`;

export const _WithFooter: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: footerCode }
        }
    },
    render: () => ({
        components: { Modal, ModalClose, Button, Field, Input },
        data: () => {
            return { isOpen: false };
        },
        template: footerCode,
    }),
};
