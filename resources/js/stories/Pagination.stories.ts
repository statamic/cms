import type {Meta, StoryObj} from '@storybook/vue3';
import {Pagination} from '@ui';
import {ref} from 'vue';

const meta = {
    title: 'Components/Pagination',
    component: Pagination,
    argTypes: {
        showTotals: {
            control: 'boolean',
            description: 'When `true`, shows the totals (eg. 1-10 of 50)',
        },
        perPage: {
            control: 'number',
            description: 'The number of items per page',
        },
        resourceMeta: {
            control: 'object',
            description: 'The `meta` object from a Laravel API resource',
        },
        scrollToTop: {
            control: 'boolean',
            description: 'When `true`, scrolls to the top when changing pages',
        },
        showPageLinks: {
            control: 'boolean',
            description: 'When `true`, shows individual page number buttons',
        },
        showPerPageSelector: {
            control: 'boolean',
            description: 'When `true`, shows the "per page" dropdown',
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
