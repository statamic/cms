import type { Meta, StoryObj } from '@storybook/vue3';
import { Tabs, TabList, TabTrigger, TabContent } from '@ui';

const meta = {
    title: 'Components/Tabs',
    component: Tabs,
    argTypes: {
        defaultTab: { control: 'text' },
    },
} satisfies Meta<typeof Tabs>;

export default meta;
type Story = StoryObj<typeof meta>;

const defaultCode = `
<Tabs default-tab="tab1" class="w-full">
    <TabList>
        <TabTrigger text="Shiny" name="tab1" />
        <TabTrigger text="Happy" name="tab2" />
        <TabTrigger text="People" name="tab3" />
    </TabList>
    <TabContent name="tab1">
        <p class="py-8">Tab 1 content</p>
    </TabContent>
    <TabContent name="tab2">
        <p class="py-8">Tab 2 content</p>
    </TabContent>
    <TabContent name="tab3">
        <p class="py-8">Tab 3 content</p>
    </TabContent>
</Tabs>
`;

export const _DocsIntro: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: defaultCode }
        }
    },
    render: () => ({
        components: { Tabs, TabList, TabTrigger, TabContent },
        template: defaultCode,
    }),
};
