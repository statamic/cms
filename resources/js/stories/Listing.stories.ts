import type {Meta, StoryObj} from '@storybook/vue3';
import {
    Badge,
    DropdownItem,
    Listing,
    ListingCustomizeColumns,
    ListingFilters,
    ListingHeaderCell,
    ListingPagination,
    ListingPresets,
    ListingPresetTrigger,
    ListingRowActions,
    ListingSearch,
    ListingTable,
    ListingTableBody,
    ListingTableHead,
    ListingToggleAll
} from '@ui';

const meta = {
    title: 'Layout/Listing',
    component: Listing,
    subcomponents: {
        ListingCustomizeColumns,
        ListingFilters,
        ListingHeaderCell,
        ListingPagination,
        ListingPresets,
        ListingPresetTrigger,
        ListingRowActions,
        ListingSearch,
        ListingTable,
        ListingTableBody,
        ListingTableHead,
        ListingToggleAll,
    },
    argTypes: {
        sortDirection: {
            control: 'select',
            options: ['asc', 'desc'],
        },
        'update:columns': {
            description: 'Event handler called when the column customizer is used.',
            table: {
                category: 'events',
                type: { summary: '(columns: Array) => void' }
            }
        },
        'update:sortColumn': {
            description: 'Event handler called when a table header is clicked.',
            table: {
                category: 'events',
                type: { summary: '(column: string) => void' }
            }
        },
        'update:sortDirection': {
            description: 'Event handler called when a table header is clicked.',
            table: {
                category: 'events',
                type: { summary: '(direction: string) => void' }
            }
        },
        'update:selections': {
            description: 'Event handler called when checkboxes are used.',
            table: {
                category: 'events',
                type: { summary: '(selections: Array) => void' }
            }
        },
        'update:searchQuery': {
            description: 'Event handler called when the search input is used.',
            table: {
                category: 'events',
                type: { summary: '(query: string) => void' }
            }
        },
        requestCompleted: {
            description: 'Event handler called when the AJAX request is completed.',
            table: {
                category: 'events',
                type: { summary: '(response) => void' }
            }
        },
        reordered: {
            description: 'Event handler called after a row has been moved. Emits an array of IDs.',
            table: {
                category: 'events',
                type: { summary: '(order: Array) => void' }
            }
        },
        refreshing: {
            description: 'Event handler called when the listing should refresh, for example when an action is completed. Useful when using the `items` prop.',
            table: {
                category: 'events',
                type: { summary: '() => void' }
            }
        },
    },
} satisfies Meta<typeof Listing>;

export default meta;
type Story = StoryObj<typeof meta>;

const defaultCode = `
<Listing 
    :items="[
        { id: 1, name: 'Jack McDade', location: 'USA 🇺🇸', role: 'Founder' },
        { id: 2, name: 'Jason Varga', location: 'USA 🇺🇸', role: 'Lead Developer' },
        { id: 3, name: 'Joshua Blum', location: 'Germany 🇩🇪', role: 'Support' },
        { id: 4, name: 'Duncan McClean', location: 'Scotland 🏴󠁧󠁢󠁳󠁣󠁴󠁿', role: 'Developer' },
        { id: 5, name: 'Jay George', location: 'England 🏴󠁧󠁢󠁥󠁮󠁧󠁿️', role: 'Developer' },
        { id: 6, name: 'David Hasselhoff', location: 'USA 🇺🇸', role: 'The Hoff' },
    ]"
    :columns="[
        { field: 'name', label: 'Name', sortable: true },
        { field: 'location', label: 'Location', sortable: true },
        { field: 'role', label: 'Role', sortable: true },
    ]"
/>
`;

// passing dropdown actions
// using a json url

export const _DocsIntro: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: defaultCode }
        }
    },
    render: () => ({
        components: { Listing },
        template: defaultCode,
    }),
};

const customCellsCode = `
<Listing 
    :items="[
        { id: 1, name: 'Jack McDade', location: 'USA 🇺🇸', role: 'Founder' },
        { id: 2, name: 'Jason Varga', location: 'USA 🇺🇸', role: 'Lead Developer' },
        { id: 3, name: 'Joshua Blum', location: 'Germany 🇩🇪', role: 'Support' },
        { id: 4, name: 'Duncan McClean', location: 'Scotland 🏴󠁧󠁢󠁳󠁣󠁴󠁿', role: 'Developer' },
        { id: 5, name: 'Jay George', location: 'England 🏴󠁧󠁢󠁥󠁮󠁧󠁿️', role: 'Developer' },
        { id: 6, name: 'David Hasselhoff', location: 'USA 🇺🇸', role: 'The Hoff' },
    ]"
    :columns="[
        { field: 'name', label: 'Name', sortable: true },
        { field: 'location', label: 'Location', sortable: true },
        { field: 'role', label: 'Role', sortable: true },
    ]"
>
    <template #cell-name="{ row, value }">
        <a class="title-index-field" href="#" v-html="value" />
    </template>

    <template #cell-role="{ row, value }">
        <Badge :text="value" pill />
    </template>
</Listing>
`;

export const _CustomCells: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: customCellsCode }
        }
    },
    render: () => ({
        components: { Listing, Badge },
        template: customCellsCode,
    }),
};

const actionsCode = `
<Listing 
    :items="[
        { id: 1, name: 'Jack McDade', location: 'USA 🇺🇸', role: 'Founder' },
        { id: 2, name: 'Jason Varga', location: 'USA 🇺🇸', role: 'Lead Developer' },
        { id: 3, name: 'Joshua Blum', location: 'Germany 🇩🇪', role: 'Support' },
        { id: 4, name: 'Duncan McClean', location: 'Scotland 🏴󠁧󠁢󠁳󠁣󠁴󠁿', role: 'Developer' },
        { id: 5, name: 'Jay George', location: 'England 🏴󠁧󠁢󠁥󠁮󠁧󠁿️', role: 'Developer' },
        { id: 6, name: 'David Hasselhoff', location: 'USA 🇺🇸', role: 'The Hoff' },
    ]"
    :columns="[
        { field: 'name', label: 'Name', sortable: true },
        { field: 'location', label: 'Location', sortable: true },
        { field: 'role', label: 'Role', sortable: true },
    ]"
>
    <template #prepended-row-actions="{ row: entry }">
        <DropdownItem text="Visit Profile" href="#" icon="eye" target="_blank" />
        <DropdownItem text="Edit" href="#" icon="edit" />
    </template>
</Listing>
`;

export const _WithActions: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: actionsCode }
        }
    },
    render: () => ({
        components: { Listing, Badge, DropdownItem },
        template: actionsCode,
    }),
};