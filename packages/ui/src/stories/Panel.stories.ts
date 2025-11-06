import type { Meta, StoryObj } from '@storybook/vue3';
import Panel from '../Panel/Panel.vue';
import PanelHeader from '../Panel/Header.vue';
import PanelFooter from '../Panel/Footer.vue';
import Button from '../Button/Button.vue';
import Input from '../Input/Input.vue';
import { ref } from 'vue';

const meta = {
  title: 'Components/Panel',
  component: Panel,
  tags: ['autodocs'],
  argTypes: {
    heading: { control: 'text' },
    subheading: { control: 'text' },
  },
} satisfies Meta<typeof Panel>;

export default meta;
type Story = StoryObj<typeof meta>;

export const Default: Story = {
  render: (args) => ({
    components: { Panel },
    setup() {
      return { args };
    },
    template: `
      <Panel v-bind="args" heading="Panel Title" subheading="This is a panel with a heading and subheading">
        <div class="p-4 bg-white dark:bg-gray-900 rounded-xl">
          <p class="text-sm">Panel content goes here.</p>
        </div>
      </Panel>
    `,
  }),
};

export const WithForm: Story = {
  render: (args) => ({
    components: { Panel, Input, Button },
    setup() {
      const name = ref('');
      const email = ref('');
      return { args, name, email };
    },
    template: `
      <Panel v-bind="args" heading="Contact Information" subheading="Update your contact details">
        <div class="p-4 bg-white dark:bg-gray-900 rounded-xl space-y-4">
          <div>
            <label class="block text-sm font-medium mb-1">Name</label>
            <Input v-model="name" placeholder="Enter your name" />
          </div>
          <div>
            <label class="block text-sm font-medium mb-1">Email</label>
            <Input v-model="email" type="email" placeholder="Enter your email" />
          </div>
          <div class="flex gap-2">
            <Button text="Save Changes" variant="primary" />
            <Button text="Cancel" variant="ghost" />
          </div>
        </div>
      </Panel>
    `,
  }),
};

export const WithFooter: Story = {
  render: (args) => ({
    components: { Panel, PanelFooter, Button },
    setup() {
      return { args };
    },
    template: `
      <Panel v-bind="args" heading="Settings">
        <div class="p-4 bg-white dark:bg-gray-900 rounded-xl">
          <p class="text-sm">Configure your application settings.</p>
        </div>
        <PanelFooter>
          <div class="flex justify-between items-center">
            <span class="text-xs text-gray-500">Last updated: 2 hours ago</span>
            <Button text="Apply" variant="primary" size="sm" />
          </div>
        </PanelFooter>
      </Panel>
    `,
  }),
};

export const WithHeaderActions: Story = {
  render: (args) => ({
    components: { Panel, Button },
    setup() {
      return { args };
    },
    template: `
      <Panel v-bind="args" heading="Dashboard" subheading="Overview of your account">
        <template #header-actions>
          <Button icon="refresh" variant="ghost" size="sm" />
          <Button icon="settings" variant="ghost" size="sm" />
        </template>
        <div class="p-4 bg-white dark:bg-gray-900 rounded-xl">
          <p class="text-sm">Dashboard content here.</p>
        </div>
      </Panel>
    `,
  }),
};

export const MultipleContent: Story = {
  render: (args) => ({
    components: { Panel },
    setup() {
      return { args };
    },
    template: `
      <Panel v-bind="args" heading="Multiple Sections">
        <div class="p-4 bg-white dark:bg-gray-900 rounded-xl mb-2">
          <h4 class="font-semibold mb-2">Section 1</h4>
          <p class="text-sm">First section content.</p>
        </div>
        <div class="p-4 bg-white dark:bg-gray-900 rounded-xl mb-2">
          <h4 class="font-semibold mb-2">Section 2</h4>
          <p class="text-sm">Second section content.</p>
        </div>
        <div class="p-4 bg-white dark:bg-gray-900 rounded-xl">
          <h4 class="font-semibold mb-2">Section 3</h4>
          <p class="text-sm">Third section content.</p>
        </div>
      </Panel>
    `,
  }),
};

