import type { Meta, StoryObj } from '@storybook/vue3';
import { SplitterGroup, SplitterPanel, SplitterResizeHandle } from '@ui';

const meta = {
    title: 'Components/Splitter',
    component: SplitterGroup,
    argTypes: {
        direction: {
            control: 'select',
            options: ['horizontal', 'vertical'],
        },
    },
} satisfies Meta<typeof SplitterGroup>;

export default meta;
type Story = StoryObj<typeof meta>;

const defaultCode = `
<SplitterGroup>
    <SplitterPanel class="h-24 bg-gray-100 rounded-xl flex items-center justify-center">
        Left
    </SplitterPanel>
    <SplitterResizeHandle class="w-3"/>
    <SplitterPanel class="h-24 bg-gray-100 rounded-xl flex items-center justify-center">
        Right
    </SplitterPanel>
</SplitterGroup>
`;

export const _DocsIntro: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: defaultCode }
        }
    },
    render: () => ({
        components: { SplitterGroup, SplitterPanel, SplitterResizeHandle },
        template: defaultCode,
    }),
};

const defaultSizeCode = `
<SplitterGroup>
    <SplitterPanel class="h-24 bg-gray-100 rounded-xl flex items-center justify-center">
        Sidebar
    </SplitterPanel>
    <SplitterResizeHandle class="w-3"/>
    <SplitterPanel :default-size="75" class="h-24 bg-gray-100 rounded-xl flex items-center justify-center">
        Main
    </SplitterPanel>
</SplitterGroup>
`;

export const _DefaultSize: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: defaultSizeCode }
        }
    },
    render: () => ({
        components: { SplitterGroup, SplitterPanel, SplitterResizeHandle },
        template: defaultSizeCode,
    }),
};

const collapsibleCode = `
<SplitterGroup>
    <SplitterPanel collapsible :min-size="15" class="h-24 bg-gray-100 rounded-xl flex items-center justify-center">
        Sidebar
    </SplitterPanel>
    <SplitterResizeHandle class="w-3"/>
    <SplitterPanel :default-size="75" class="h-24 bg-gray-100 rounded-xl flex items-center justify-center">
        Main
    </SplitterPanel>
</SplitterGroup>
`;

export const _Collapsible: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: collapsibleCode }
        }
    },
    render: () => ({
        components: { SplitterGroup, SplitterPanel, SplitterResizeHandle },
        template: collapsibleCode,
    }),
};

const nestedCode = `
<SplitterGroup class="p-4">
    <SplitterPanel class="bg-gray-100 rounded-xl h-48 flex items-center justify-center">
        Left
    </SplitterPanel>
    <SplitterResizeHandle class="w-3"/>
    <SplitterPanel>
        <SplitterGroup direction="vertical">
            <SplitterPanel class="bg-gray-100 rounded-xl flex items-center justify-center">
                Right Top
            </SplitterPanel>
            <SplitterResizeHandle class="h-3" />
            <SplitterPanel class="bg-gray-100 dark:bg-gray-900 rounded-xl flex items-center justify-center">
                Right Bottom
            </SplitterPanel>
        </SplitterGroup>
    </SplitterPanel>
</SplitterGroup>
`;

export const _Nested: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: nestedCode }
        }
    },
    render: () => ({
        components: { SplitterGroup, SplitterPanel, SplitterResizeHandle },
        template: nestedCode,
    }),
};
