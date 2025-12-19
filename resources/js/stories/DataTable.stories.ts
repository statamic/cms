import type {Meta, StoryObj} from '@storybook/vue3';
import {DataTable, DataTableCell, DataTableColumn, DataTableRow} from '@ui';

const meta = {
    title: 'Layout/DataTable',
    component: DataTable,
    subcomponents: {
        DataTableColumn,
        DataTableRow,
        DataTableCell,
    },
    argTypes: {
        variant: {
            control: 'select',
            options: ['normal', 'compact'],
        },
    },
} satisfies Meta<typeof DataTable>;

export default meta;
type Story = StoryObj<typeof meta>;

const defaultCode = `
<DataTable>
    <template #columns>
        <DataTableColumn>Name</DataTableColumn>
        <DataTableColumn>GitHub</DataTableColumn>
        <DataTableColumn align="right">Role</DataTableColumn>
    </template>
    <template #rows>
        <DataTableRow :index="0" :total="3">
            <template #default="{ position, rightPosition }">
                <DataTableCell :index="0" :position="position" :rightPosition="rightPosition">
                    Jack McDade
                </DataTableCell>
                <DataTableCell :index="1" :position="position" :rightPosition="rightPosition">
                    @jackmcdade
                </DataTableCell>
                <DataTableCell :index="2" :position="position" :rightPosition="rightPosition" align="right">
                    Founder
                </DataTableCell>
            </template>
        </DataTableRow>
        <DataTableRow :index="1" :total="3">
            <template #default="{ position, rightPosition }">
                <DataTableCell :index="0" :position="position" :rightPosition="rightPosition">
                    Jason Varga
                </DataTableCell>
                <DataTableCell :index="1" :position="position" :rightPosition="rightPosition">
                    @jasonvarga
                </DataTableCell>
                <DataTableCell :index="2" :position="position" :rightPosition="rightPosition" align="right">
                    Lead Developer
                </DataTableCell>
            </template>
        </DataTableRow>
        <DataTableRow :index="2" :total="3">
            <template #default="{ position, rightPosition }">
                <DataTableCell :index="0" :position="position" :rightPosition="rightPosition">
                    Duncan McClean
                </DataTableCell>
                <DataTableCell :index="1" :position="position" :rightPosition="rightPosition">
                    @duncanmcclean
                </DataTableCell>
                <DataTableCell :index="2" :position="position" :rightPosition="rightPosition" align="right">
                    Developer
                </DataTableCell>
            </template>
        </DataTableRow>
    </template>
</DataTable>
`;

export const _DocsIntro: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: defaultCode }
        }
    },
    render: () => ({
        components: { DataTable, DataTableColumn, DataTableRow, DataTableCell },
        template: defaultCode,
    }),
};

const compactCode = `
<DataTable variant="compact">
    <template #columns>
        <DataTableColumn>Product</DataTableColumn>
        <DataTableColumn>SKU</DataTableColumn>
        <DataTableColumn align="right">Stock</DataTableColumn>
    </template>
    <template #rows>
        <DataTableRow :index="0" :total="2">
            <template #default="{ position, rightPosition }">
                <DataTableCell :index="0" :position="position" :rightPosition="rightPosition">
                    T-Shirt
                </DataTableCell>
                <DataTableCell :index="1" :position="position" :rightPosition="rightPosition">
                    TSH-001
                </DataTableCell>
                <DataTableCell :index="2" :position="position" :rightPosition="rightPosition" align="right">
                    42
                </DataTableCell>
            </template>
        </DataTableRow>
        <DataTableRow :index="1" :total="2">
            <template #default="{ position, rightPosition }">
                <DataTableCell :index="0" :position="position" :rightPosition="rightPosition">
                    Hoodie
                </DataTableCell>
                <DataTableCell :index="1" :position="position" :rightPosition="rightPosition">
                    HOD-002
                </DataTableCell>
                <DataTableCell :index="2" :position="position" :rightPosition="rightPosition" align="right">
                    18
                </DataTableCell>
            </template>
        </DataTableRow>
    </template>
</DataTable>
`;

export const _Compact: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: compactCode }
        }
    },
    render: () => ({
        components: { DataTable, DataTableColumn, DataTableRow, DataTableCell },
        template: compactCode,
    }),
};

const columnWidthCode = `
<DataTable>
    <template #columns>
        <DataTableColumn width="60%">Title</DataTableColumn>
        <DataTableColumn width="20%">Status</DataTableColumn>
        <DataTableColumn width="20%" align="right">Date</DataTableColumn>
    </template>
    <template #rows>
        <DataTableRow :index="0" :total="2">
            <template #default="{ position, rightPosition }">
                <DataTableCell :index="0" :position="position" :rightPosition="rightPosition">
                    Statamic 6 is Out!
                </DataTableCell>
                <DataTableCell :index="1" :position="position" :rightPosition="rightPosition">
                    Draft
                </DataTableCell>
                <DataTableCell :index="2" :position="position" :rightPosition="rightPosition" align="right">
                    January 5th, 2026
                </DataTableCell>
            </template>
        </DataTableRow>
        <DataTableRow :index="0" :total="2">
            <template #default="{ position, rightPosition }">
                <DataTableCell :index="0" :position="position" :rightPosition="rightPosition">
                    Statamic 6 Sneak Peek
                </DataTableCell>
                <DataTableCell :index="1" :position="position" :rightPosition="rightPosition">
                    Published
                </DataTableCell>
                <DataTableCell :index="2" :position="position" :rightPosition="rightPosition" align="right">
                    July 14th, 2025
                </DataTableCell>
            </template>
        </DataTableRow>
        <DataTableRow :index="1" :total="2">
            <template #default="{ position, rightPosition }">
                <DataTableCell :index="0" :position="position" :rightPosition="rightPosition">
                    The Brand New Statamic Experience
                </DataTableCell>
                <DataTableCell :index="1" :position="position" :rightPosition="rightPosition">
                    Published
                </DataTableCell>
                <DataTableCell :index="2" :position="position" :rightPosition="rightPosition" align="right">
                    December 10th, 2024
                </DataTableCell>
            </template>
        </DataTableRow>
    </template>
</DataTable>
`;

export const _ColumnWidth: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: columnWidthCode }
        }
    },
    render: () => ({
        components: { DataTable, DataTableColumn, DataTableRow, DataTableCell },
        template: columnWidthCode,
    }),
};
