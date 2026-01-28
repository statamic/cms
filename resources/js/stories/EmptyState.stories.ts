import type {Meta, StoryObj} from '@storybook/vue3';
import {EmptyStateItem, EmptyStateMenu} from '@ui';

const meta = {
    title: 'Layout/EmptyState',
    component: EmptyStateMenu,
    subcomponents: {
        EmptyStateItem,
    },
    argTypes: {},
} satisfies Meta<typeof EmptyStateMenu>;

export default meta;
type Story = StoryObj<typeof meta>;

const defaultCode = `
<EmptyStateMenu heading="Start designing your collection with these steps">
    <EmptyStateItem
        icon="configure"
        heading="Configure Collection"
        description="Configure URLs and routes, define blueprints, date behaviors, ordering and other options."
        :href="cp_url('collections/blog/edit')"
    />
    <EmptyStateItem
        icon="fieldtype-entries"
        heading="Create Entry"
        description="Create the first entry or stub out a handful of placeholder entries - whatever suits your needs."
        :href="cp_url('collections/blog/create')"
    />
    <EmptyStateItem
        icon="blueprints"
        heading="Configure Blueprints"
        description="Manage the blueprints and fields available for this collection. Keep organized with fieldsets."
        :href="cp_url('collections/blog/blueprints')"
    />
    <EmptyStateItem
        icon="scaffold"
        heading="Scaffold Views"
        description="Generate index and detail views from the collection name at the click of a button."
        :href="cp_url('collections/blog/scaffold')"
    />
</EmptyStateMenu>
`;

export const _DocsIntro: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: defaultCode }
        }
    },
    render: () => ({
        components: { EmptyStateMenu, EmptyStateItem },
        // todo: architecutal background
        template: defaultCode,
    }),
};

const withSlotCode = `
<EmptyStateMenu heading="Start designing your collection with these steps">
    <EmptyStateItem
        icon="fieldtype-entries"
        heading="Create Entry"
        description="Create the first entry or stub out a handful of placeholder entries - whatever suits your needs."
    >
        <a href="#" class="text-blue-600 text-sm rtl:ml-2 ltr:mr-2">Post</a>
        <a href="#" class="text-blue-600 text-sm rtl:ml-2 ltr:mr-2">Link</a>
        <a href="#" class="text-blue-600 text-sm rtl:ml-2 ltr:mr-2">Video</a>
    </EmptyStateItem>
    <EmptyStateItem
        icon="blueprints"
        heading="Configure Blueprints"
        description="Manage the blueprints and fields available for this collection. Keep organized with fieldsets."
        :href="cp_url('collections/blog/blueprints')"
    />
</EmptyStateMenu>
`;

export const _WithSlot: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: withSlotCode }
        }
    },
    render: () => ({
        components: { EmptyStateMenu, EmptyStateItem },
        template: withSlotCode,
    }),
};

const noLinksCode = `
<EmptyStateMenu heading="Start designing your collection with these steps">
    <EmptyStateItem
        icon="fieldtype-entries"
        heading="Create Entry"
        description="Create the first entry or stub out a handful of placeholder entries - whatever suits your needs."
        @click="createEntry"
    />
</EmptyStateMenu>
`;

export const _NoLinks: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: noLinksCode }
        }
    },
    render: () => ({
        components: { EmptyStateMenu, EmptyStateItem },
        methods: {
            createEntry() {
                //
            }
        },
        template: noLinksCode,
    }),
};
