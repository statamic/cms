import type {Meta, StoryObj} from '@storybook/vue3';
import {CreateForm} from '@ui';
import {icons} from "@/stories/icons";

const meta = {
    title: 'Components/CreateForm',
    component: CreateForm,
    argTypes: {
        icon: {
            control: 'select',
            options: icons,
        },
    },
} satisfies Meta<typeof CreateForm>;

export default meta;
type Story = StoryObj<typeof meta>;

const defaultCode = `
<CreateForm 
    title="Create Collection"
    subtitle="Collections are containers that hold entries"
    icon="collections"
    :route="cp_url('collections/create')"
/>
`;

export const _DocsIntro: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: defaultCode }
        }
    },
    render: () => ({
        components: { CreateForm },
        template: defaultCode,
    }),
};

const withoutHandleCode = `
<CreateForm 
    without-handle
    title="Create Blueprint"
    subtitle="Blueprints define and organize fields into content models"
    icon="blueprints"
    :route="cp_url('blueprints/create')"
/>
`;

export const _WithoutHandle: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: withoutHandleCode }
        }
    },
    render: () => ({
        components: { CreateForm },
        template: withoutHandleCode,
    }),
};

const withInstructionsCode = `
<CreateForm 
    title="Create Collection"
    subtitle="Collections are containers that hold entries"
    icon="collections"
    :route="cp_url('collections/create')"
    title-instructions="The display name for this collection"
    handle-instructions="Used in URLs and code. Can only contain letters, numbers, and underscores"
/>
`;

export const _WithInstructions: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: withInstructionsCode }
        }
    },
    render: () => ({
        components: { CreateForm },
        template: withInstructionsCode,
    }),
};
