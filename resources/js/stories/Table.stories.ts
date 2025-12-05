import type { Meta, StoryObj } from '@storybook/vue3';
import { Table, TableColumns, TableColumn, TableRows, TableRow, TableCell, Badge } from '@ui';

const meta = {
    title: 'Components/Table',
    component: Table,
    argTypes: {},
} satisfies Meta<typeof Table>;

export default meta;
type Story = StoryObj<typeof meta>;

const defaultCode = `
<Table>
    <TableColumns>
        <TableColumn>Product</TableColumn>
        <TableColumn>Stock</TableColumn>
        <TableColumn class="text-right">Price</TableColumn>
    </TableColumns>
    <TableRows>
        <TableRow>
            <TableCell>Mechanical Keyboard</TableCell>
            <TableCell>
                <Badge color="green" pill>In Stock</Badge>
            </TableCell>
            <TableCell class="text-right font-semibold text-black">$159.00</TableCell>
        </TableRow>
        <TableRow>
            <TableCell>Ergonomic Mouse</TableCell>
            <TableCell>
                <Badge color="red" pill>Out of Stock</Badge>
            </TableCell>
            <TableCell class="text-right font-semibold text-black">$89.00</TableCell>
        </TableRow>
        <TableRow>
            <TableCell>4K Monitor</TableCell>
            <TableCell>
                <Badge color="yellow" pill>Low Stock</Badge>
            </TableCell>
            <TableCell class="text-right font-semibold text-black">$349.00</TableCell>
        </TableRow>
    </TableRows>
</Table>
`;

export const _DocsIntro: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: defaultCode }
        }
    },
    render: () => ({
        components: { Table, TableColumns, TableColumn, TableRows, TableRow, TableCell, Badge },
        template: defaultCode,
    }),
};
