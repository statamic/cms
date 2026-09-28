import type {Meta, StoryObj} from '@storybook/vue3';
import {SplitterGroup, SplitterPanel, SplitterResizeHandle} from '@statamic/cms/ui';

const meta = {
    title: 'Layout/Splitter',
    component: SplitterGroup,
    subcomponents: { SplitterPanel },
    argTypes: {
        direction: {
            control: 'select',
            options: ['horizontal', 'vertical'],
        },
    },
} satisfies Meta<typeof SplitterGroup>;

export default meta;
type Story = StoryObj<typeof meta>;

export const _DocsIntro: Story = {
    tags: ['!dev'],
    render: () => ({
        components: { SplitterGroup, SplitterPanel, SplitterResizeHandle },
        template: `
            <SplitterGroup>
                <SplitterPanel class="h-24 bg-gray-100 rounded-xl flex items-center justify-center">
                    Left
                </SplitterPanel>
                <SplitterResizeHandle class="w-3"/>
                <SplitterPanel class="h-24 bg-gray-100 rounded-xl flex items-center justify-center">
                    Right
                </SplitterPanel>
            </SplitterGroup>
        `,
    }),
};

export const _DefaultSize: Story = {
    tags: ['!dev'],
    render: () => ({
        components: { SplitterGroup, SplitterPanel, SplitterResizeHandle },
        template: `
            <SplitterGroup>
                <SplitterPanel class="h-24 bg-gray-100 rounded-xl flex items-center justify-center">
                    Sidebar
                </SplitterPanel>
                <SplitterResizeHandle class="w-3"/>
                <SplitterPanel :default-size="75" class="h-24 bg-gray-100 rounded-xl flex items-center justify-center">
                    Main
                </SplitterPanel>
            </SplitterGroup>
        `,
    }),
};

export const _Collapsible: Story = {
    tags: ['!dev'],
    render: () => ({
        components: { SplitterGroup, SplitterPanel, SplitterResizeHandle },
        template: `
            <SplitterGroup>
                <SplitterPanel collapsible :min-size="15" class="h-24 bg-gray-100 rounded-xl flex items-center justify-center">
                    Sidebar
                </SplitterPanel>
                <SplitterResizeHandle class="w-3"/>
                <SplitterPanel :default-size="75" class="h-24 bg-gray-100 rounded-xl flex items-center justify-center">
                    Main
                </SplitterPanel>
            </SplitterGroup>
        `,
    }),
};

export const _Nested: Story = {
    tags: ['!dev'],
    render: () => ({
        components: { SplitterGroup, SplitterPanel, SplitterResizeHandle },
        template: `
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
        `,
    }),
};
