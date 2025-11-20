import type { Meta, StoryObj } from '@storybook/vue3';
import { ref } from 'vue';
import Tabs from '@statamic/ui/Tabs/Tabs.vue';
import TabList from '@statamic/ui/Tabs/List.vue';
import TabTrigger from '@statamic/ui/Tabs/Trigger.vue';
import TabContent from '@statamic/ui/Tabs/Content.vue';

const meta = {
  title: 'Components/Tabs',
  component: Tabs,
  tags: ['autodocs'],
} satisfies Meta<typeof Tabs>;

export default meta;
type Story = StoryObj<typeof meta>;

export const Default: Story = {
  render: (args) => ({
    components: { Tabs, TabList, TabTrigger, TabContent },
    setup() {
      const activeTab = ref('tab1');
      return { args, activeTab };
    },
    template: `
      <Tabs v-bind="args" v-model="activeTab">
        <TabList>
          <TabTrigger value="tab1">First Tab</TabTrigger>
          <TabTrigger value="tab2">Second Tab</TabTrigger>
          <TabTrigger value="tab3">Third Tab</TabTrigger>
        </TabList>
        <TabContent value="tab1">
          <div class="p-4">
            <h3 class="font-semibold mb-2">First Tab Content</h3>
            <p>This is the content for the first tab.</p>
          </div>
        </TabContent>
        <TabContent value="tab2">
          <div class="p-4">
            <h3 class="font-semibold mb-2">Second Tab Content</h3>
            <p>This is the content for the second tab.</p>
          </div>
        </TabContent>
        <TabContent value="tab3">
          <div class="p-4">
            <h3 class="font-semibold mb-2">Third Tab Content</h3>
            <p>This is the content for the third tab.</p>
          </div>
        </TabContent>
      </Tabs>
    `,
  }),
};

export const WithIcons: Story = {
  render: (args) => ({
    components: { Tabs, TabList, TabTrigger, TabContent },
    setup() {
      const activeTab = ref('profile');
      return { args, activeTab };
    },
    template: `
      <Tabs v-bind="args" v-model="activeTab">
        <TabList>
          <TabTrigger value="profile">
            <span class="flex items-center gap-2">
              <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
              Profile
            </span>
          </TabTrigger>
          <TabTrigger value="settings">
            <span class="flex items-center gap-2">
              <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
              Settings
            </span>
          </TabTrigger>
        </TabList>
        <TabContent value="profile">
          <div class="p-4">
            <h3 class="font-semibold mb-2">Profile Settings</h3>
            <p>Manage your profile information.</p>
          </div>
        </TabContent>
        <TabContent value="settings">
          <div class="p-4">
            <h3 class="font-semibold mb-2">Application Settings</h3>
            <p>Configure your application preferences.</p>
          </div>
        </TabContent>
      </Tabs>
    `,
  }),
};

export const Controlled: Story = {
  render: (args) => ({
    components: { Tabs, TabList, TabTrigger, TabContent },
    setup() {
      const activeTab = ref('overview');
      return { args, activeTab };
    },
    template: `
      <div>
        <div class="mb-4 flex gap-2">
          <button @click="activeTab = 'overview'" class="px-3 py-1 bg-gray-200 rounded">Go to Overview</button>
          <button @click="activeTab = 'details'" class="px-3 py-1 bg-gray-200 rounded">Go to Details</button>
          <button @click="activeTab = 'stats'" class="px-3 py-1 bg-gray-200 rounded">Go to Stats</button>
        </div>
        <Tabs v-bind="args" v-model="activeTab">
          <TabList>
            <TabTrigger value="overview">Overview</TabTrigger>
            <TabTrigger value="details">Details</TabTrigger>
            <TabTrigger value="stats">Statistics</TabTrigger>
          </TabList>
          <TabContent value="overview">
            <div class="p-4">Overview content</div>
          </TabContent>
          <TabContent value="details">
            <div class="p-4">Details content</div>
          </TabContent>
          <TabContent value="stats">
            <div class="p-4">Statistics content</div>
          </TabContent>
        </Tabs>
      </div>
    `,
  }),
};

