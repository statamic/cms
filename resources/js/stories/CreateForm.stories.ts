import type {Meta, StoryObj} from '@storybook/vue3';
import {CreateForm} from '@ui';
import {icons} from "@/stories/icons";

/**
 * @import import { CreateForm } from '@statamic/cms/ui';
 */
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

export const _DocsIntro: Story = {
    tags: ['!dev'],
    render: () => ({
        components: { CreateForm },
        template: `
            <CreateForm 
                title="Create Collection"
                subtitle="Collections are containers that hold entries representing articles, blog posts, products, events, or any other content type."
                icon="collections"
                :route="cp_url('collections/create')"
            />
        `,
    }),
};

export const _WithoutHandle: Story = {
    tags: ['!dev'],
    render: () => ({
        components: { CreateForm },
        template: `
            <CreateForm 
                without-handle
                title="Create Blueprint"
                subtitle="Blueprints define and organize fields into content models for collections, forms, and other data."
                icon="blueprints"
                :route="cp_url('blueprints/create')"
            />
        `,
    }),
};

export const _WithInstructions: Story = {
    tags: ['!dev'],
    render: () => ({
        components: { CreateForm },
        template: `
            <CreateForm 
                title="Create Collection"
                subtitle="Collections are containers that hold entries representing articles, blog posts, products, events, or any other content type."
                icon="collections"
                :route="cp_url('collections/create')"
                title-instructions="The display name for this collection"
                handle-instructions="Used in URLs and code. Can only contain letters, numbers, and underscores"
            />
        `,
    }),
};
