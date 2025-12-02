import type { Meta, StoryObj } from '@storybook/vue3';
import { Tabs, TabList, TabTrigger, TabContent, TabsIndicator, Icon } from '@ui';

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

const indicatorCode = `
<Tabs default-tab="tab1" class="text-center text-gray-700">
    <TabsList class="border-none! space-x-2!">
        <TabsTrigger name="tab1" class="p-4! z-10">
            <Icon name="avatar" class="size-8" />
        </TabsTrigger>
        <TabsTrigger name="tab2" class="p-4! z-10">
            <Icon name="mail" class="size-8" />
        </TabsTrigger>
        <TabsTrigger name="tab3" class="p-4! z-10">
            <Icon name="cog" class="size-8" />
        </TabsTrigger>
        <TabsIndicator class="absolute left-0 z-0 h-full bg-gray-100 bottom-0 w-[var(--reka-tabs-indicator-size)] translate-x-[var(--reka-tabs-indicator-position)] translate-y-[1px] rounded-lg transition duration-300" />
    </TabsList>
    <TabsContent name="tab1">
        <p>Tab 1 content</p>
    </TabsContent>
    <TabsContent name="tab2">
        <p>Tab 2 content</p>
    </TabsContent>
    <TabsContent name="tab3">
        <p>Tab 3 content</p>
    </TabsContent>
</Tabs>
`;

export const _TabIndicator: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: indicatorCode }
        }
    },
    render: () => ({
        components: { Tabs, TabList, TabTrigger, TabContent, TabsIndicator, Icon },
        template: indicatorCode,
    }),
};
