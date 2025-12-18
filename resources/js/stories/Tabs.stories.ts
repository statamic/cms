import type {Meta, StoryObj} from '@storybook/vue3';
import {TabContent, TabList, Tabs, TabTrigger} from '@ui';
import {ref} from 'vue';

const meta = {
    title: 'Components/Tabs',
    component: Tabs,
    subcomponents: {
        TabList,
        TabTrigger,
        TabContent,
    },
    argTypes: {
        'update:modelValue': {
            description: 'Event handler called when the tab changes.',
            table: {
                category: 'events',
                type: { summary: '(value: string) => void' }
            }
        }
    },
} satisfies Meta<typeof Tabs>;

export default meta;
type Story = StoryObj<typeof meta>;

const defaultCode = `
<Tabs v-model="activeTab" class="w-full">
    <TabList>
        <TabTrigger text="Shiny" name="one" />
        <TabTrigger text="Happy" name="two" />
        <TabTrigger text="People" name="three" />
    </TabList>
    <TabContent name="one">
        <p class="py-8">Content of Tab 1</p>
    </TabContent>
    <TabContent name="two">
        <p class="py-8">Content of Tab 2</p>
    </TabContent>
    <TabContent name="three">
        <p class="py-8">Content of Tab 3</p>
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
        setup() {
            const activeTab = ref('tab1');
            return { activeTab };
        },
        template: defaultCode,
    }),
};
