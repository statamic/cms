import type {Meta, StoryObj} from '@storybook/vue3';
import {Pagination} from '@ui';
import {ref} from 'vue';

const meta = {
    title: 'Layout/Pagination',
    component: Pagination,
    argTypes: {
        'page-selected': {
            description: 'Event handler called when a page is selected.',
            table: {
                category: 'events',
                type: { summary: '(page: number) => void' }
            }
        },
        'per-page-changed': {
            description: 'Event handler called when the per page value is changed.',
            table: {
                category: 'events',
                type: { summary: '(perPage: number) => void' }
            }
        },
    },
} satisfies Meta<typeof Pagination>;

export default meta;
type Story = StoryObj<typeof meta>;

const createResourceMeta = (currentPage: number, total: number, perPage: number) => ({
    current_page: currentPage,
    from: (currentPage - 1) * perPage + 1,
    to: Math.min(currentPage * perPage, total),
    total,
    last_page: Math.ceil(total / perPage),
});

export const _DocsIntro: Story = {
    tags: ['!dev'],
    render: () => ({
        components: { Pagination },
        setup() {
            const currentPage = ref(1);
            const perPage = ref(25);
            const resourceMeta = ref(createResourceMeta(currentPage.value, 100, perPage.value));

            const handlePageSelected = (page: number) => {
                currentPage.value = page;
                resourceMeta.value = createResourceMeta(page, 100, perPage.value);
            };

            const handlePerPageChanged = (newPerPage: number) => {
                perPage.value = newPerPage;
                currentPage.value = 1;
                resourceMeta.value = createResourceMeta(1, 100, newPerPage);
            };

            return { resourceMeta, perPage, handlePageSelected, handlePerPageChanged };
        },
        template: `
            <Pagination
                :resource-meta="resourceMeta"
                :per-page="perPage"
                @page-selected="handlePageSelected"
                @per-page-changed="handlePerPageChanged"
            />
        `,
    }),
};

export const Default: Story = {
    tags: ['!dev'],
    render: () => ({
        components: { Pagination },
        setup() {
            const currentPage = ref(1);
            const perPage = ref(25);
            const resourceMeta = ref(createResourceMeta(currentPage.value, 100, perPage.value));

            const handlePageSelected = (page: number) => {
                currentPage.value = page;
                resourceMeta.value = createResourceMeta(page, 100, perPage.value);
            };

            const handlePerPageChanged = (newPerPage: number) => {
                perPage.value = newPerPage;
                currentPage.value = 1;
                resourceMeta.value = createResourceMeta(1, 100, newPerPage);
            };

            return { resourceMeta, perPage, handlePageSelected, handlePerPageChanged };
        },
        template: `
            <Pagination
                :resource-meta="resourceMeta"
                :per-page="perPage"
                @page-selected="handlePageSelected"
                @per-page-changed="handlePerPageChanged"
            />
        `,
    }),
};

export const ManyPages: Story = {
    tags: ['!dev'],
    render: () => ({
        components: { Pagination },
        setup() {
            const currentPage = ref(5);
            const perPage = ref(25);
            const resourceMeta = ref(createResourceMeta(currentPage.value, 500, perPage.value));

            const handlePageSelected = (page: number) => {
                currentPage.value = page;
                resourceMeta.value = createResourceMeta(page, 500, perPage.value);
            };

            const handlePerPageChanged = (newPerPage: number) => {
                perPage.value = newPerPage;
                currentPage.value = 1;
                resourceMeta.value = createResourceMeta(1, 500, newPerPage);
            };

            return { resourceMeta, perPage, handlePageSelected, handlePerPageChanged };
        },
        template: `
            <Pagination
                :resource-meta="resourceMeta"
                :per-page="perPage"
                @page-selected="handlePageSelected"
                @per-page-changed="handlePerPageChanged"
            />
        `,
    }),
};

export const WithoutPageLinks: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: {
                code: `
                    <Pagination
                        :resource-meta="resourceMeta"
                        :per-page="perPage"
                        :show-page-links="false"
                        @page-selected="handlePageSelected"
                        @per-page-changed="handlePerPageChanged"
                    />
                `,
            },
        },
    },
    render: () => ({
        components: { Pagination },
        setup() {
            const currentPage = ref(1);
            const perPage = ref(25);
            const resourceMeta = ref(createResourceMeta(currentPage.value, 100, perPage.value));

            const handlePageSelected = (page: number) => {
                currentPage.value = page;
                resourceMeta.value = createResourceMeta(page, 100, perPage.value);
            };

            const handlePerPageChanged = (newPerPage: number) => {
                perPage.value = newPerPage;
                currentPage.value = 1;
                resourceMeta.value = createResourceMeta(1, 100, newPerPage);
            };

            return { resourceMeta, perPage, handlePageSelected, handlePerPageChanged };
        },
        template: `
            <Pagination
                :resource-meta="resourceMeta"
                :per-page="perPage"
                :show-page-links="false"
                @page-selected="handlePageSelected"
                @per-page-changed="handlePerPageChanged"
            />
        `,
    }),
};

export const WithoutPerPageSelector: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: {
                code: `
                    <Pagination
                        :resource-meta="resourceMeta"
                        :show-per-page-selector="false"
                        @page-selected="handlePageSelected"
                    />
                `,
            },
        },
    },
    render: () => ({
        components: { Pagination },
        setup() {
            const currentPage = ref(1);
            const resourceMeta = ref(createResourceMeta(currentPage.value, 100, 25));

            const handlePageSelected = (page: number) => {
                currentPage.value = page;
                resourceMeta.value = createResourceMeta(page, 100, 25);
            };

            return { resourceMeta, handlePageSelected };
        },
        template: `
            <Pagination
                :resource-meta="resourceMeta"
                :show-per-page-selector="false"
                @page-selected="handlePageSelected"
            />
        `,
    }),
};
