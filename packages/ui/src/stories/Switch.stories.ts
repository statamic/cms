import type { Meta, StoryObj } from '@storybook/vue3';
import { ref } from 'vue';
import Switch from '../Switch.vue';

const meta = {
  title: 'Components/Switch',
  component: Switch,
  tags: ['autodocs'],
  argTypes: {
    size: {
      control: 'select',
      options: ['xs', 'sm', 'base', 'lg'],
    },
    modelValue: { control: 'boolean' },
  },
} satisfies Meta<typeof Switch>;

export default meta;
type Story = StoryObj<typeof meta>;

export const Default: Story = {
  render: (args) => ({
    components: { Switch },
    setup() {
      const enabled = ref(false);
      return { args, enabled };
    },
    template: `
      <div class="flex items-center gap-2">
        <Switch v-bind="args" v-model="enabled" />
        <span class="text-sm">{{ enabled ? 'Enabled' : 'Disabled' }}</span>
      </div>
    `,
  }),
};

export const Checked: Story = {
  render: (args) => ({
    components: { Switch },
    setup() {
      const enabled = ref(true);
      return { args, enabled };
    },
    template: `
      <div class="flex items-center gap-2">
        <Switch v-bind="args" v-model="enabled" />
        <span class="text-sm">Notifications enabled</span>
      </div>
    `,
  }),
};

export const Sizes: Story = {
  render: () => ({
    components: { Switch },
    setup() {
      const xs = ref(true);
      const sm = ref(true);
      const base = ref(true);
      const lg = ref(true);
      return { xs, sm, base, lg };
    },
    template: `
      <div class="space-y-4">
        <div class="flex items-center gap-2">
          <Switch v-model="xs" size="xs" />
          <span class="text-xs">Extra Small</span>
        </div>
        <div class="flex items-center gap-2">
          <Switch v-model="sm" size="sm" />
          <span class="text-sm">Small</span>
        </div>
        <div class="flex items-center gap-2">
          <Switch v-model="base" size="base" />
          <span class="text-base">Base</span>
        </div>
        <div class="flex items-center gap-2">
          <Switch v-model="lg" size="lg" />
          <span class="text-lg">Large</span>
        </div>
      </div>
    `,
  }),
};

export const WithLabels: Story = {
  render: () => ({
    components: { Switch },
    setup() {
      const darkMode = ref(false);
      const notifications = ref(true);
      const autoSave = ref(true);
      return { darkMode, notifications, autoSave };
    },
    template: `
      <div class="space-y-4">
        <div class="flex items-center justify-between p-4 bg-white dark:bg-gray-900 rounded-lg">
          <div>
            <div class="font-medium">Dark Mode</div>
            <div class="text-sm text-gray-500">Enable dark theme</div>
          </div>
          <Switch v-model="darkMode" />
        </div>
        <div class="flex items-center justify-between p-4 bg-white dark:bg-gray-900 rounded-lg">
          <div>
            <div class="font-medium">Notifications</div>
            <div class="text-sm text-gray-500">Receive email notifications</div>
          </div>
          <Switch v-model="notifications" />
        </div>
        <div class="flex items-center justify-between p-4 bg-white dark:bg-gray-900 rounded-lg">
          <div>
            <div class="font-medium">Auto-save</div>
            <div class="text-sm text-gray-500">Automatically save changes</div>
          </div>
          <Switch v-model="autoSave" />
        </div>
      </div>
    `,
  }),
};

