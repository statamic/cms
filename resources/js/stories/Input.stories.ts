import type { Meta, StoryObj } from '@storybook/vue3';
import { ref } from 'vue';
import Input from '@statamic/ui/Input/Input.vue';

const meta = {
  title: 'Components/Input',
  component: Input,
  tags: ['autodocs'],
  argTypes: {
    size: {
      control: 'select',
      options: ['base', 'sm', 'xs'],
    },
    variant: {
      control: 'select',
      options: ['default', 'light', 'ghost'],
    },
    type: { control: 'text' },
    placeholder: { control: 'text' },
    disabled: { control: 'boolean' },
    readOnly: { control: 'boolean' },
    clearable: { control: 'boolean' },
    copyable: { control: 'boolean' },
    viewable: { control: 'boolean' },
    loading: { control: 'boolean' },
  },
} satisfies Meta<typeof Input>;

export default meta;
type Story = StoryObj<typeof meta>;

export const Default: Story = {
  render: (args) => ({
    components: { Input },
    setup() {
      const value = ref('');
      return { args, value };
    },
    template: '<Input v-bind="args" v-model="value" />',
  }),
  args: {
    placeholder: 'Enter text...',
  },
};

export const WithValue: Story = {
  render: (args) => ({
    components: { Input },
    setup() {
      const value = ref('Hello World');
      return { args, value };
    },
    template: '<Input v-bind="args" v-model="value" />',
  }),
};

export const WithIcon: Story = {
  render: (args) => ({
    components: { Input },
    setup() {
      const value = ref('');
      return { args, value };
    },
    template: '<Input v-bind="args" v-model="value" />',
  }),
  args: {
    icon: 'search',
    placeholder: 'Search...',
  },
};

export const WithPrependedIcon: Story = {
  render: (args) => ({
    components: { Input },
    setup() {
      const value = ref('');
      return { args, value };
    },
    template: '<Input v-bind="args" v-model="value" />',
  }),
  args: {
    iconPrepend: 'mail',
    placeholder: 'Email address',
  },
};

export const WithAppendedIcon: Story = {
  render: (args) => ({
    components: { Input },
    setup() {
      const value = ref('');
      return { args, value };
    },
    template: '<Input v-bind="args" v-model="value" />',
  }),
  args: {
    iconAppend: 'check',
    placeholder: 'Valid input',
  },
};

export const Clearable: Story = {
  render: (args) => ({
    components: { Input },
    setup() {
      const value = ref('Clear me!');
      return { args, value };
    },
    template: '<Input v-bind="args" v-model="value" />',
  }),
  args: {
    clearable: true,
  },
};

export const Copyable: Story = {
  render: (args) => ({
    components: { Input },
    setup() {
      const value = ref('Click to copy this text');
      return { args, value };
    },
    template: '<Input v-bind="args" v-model="value" />',
  }),
  args: {
    copyable: true,
  },
};

export const Password: Story = {
  render: (args) => ({
    components: { Input },
    setup() {
      const value = ref('secret123');
      return { args, value };
    },
    template: '<Input v-bind="args" v-model="value" />',
  }),
  args: {
    type: 'password',
    viewable: true,
    placeholder: 'Enter password',
  },
};

export const WithLimit: Story = {
  render: (args) => ({
    components: { Input },
    setup() {
      const value = ref('Some text');
      return { args, value };
    },
    template: '<Input v-bind="args" v-model="value" />',
  }),
  args: {
    limit: 50,
    placeholder: 'Max 50 characters',
  },
};

export const Disabled: Story = {
  render: (args) => ({
    components: { Input },
    setup() {
      const value = ref('Disabled input');
      return { args, value };
    },
    template: '<Input v-bind="args" v-model="value" />',
  }),
  args: {
    disabled: true,
  },
};

export const ReadOnly: Story = {
  render: (args) => ({
    components: { Input },
    setup() {
      const value = ref('Read only text');
      return { args, value };
    },
    template: '<Input v-bind="args" v-model="value" />',
  }),
  args: {
    readOnly: true,
  },
};

export const Sizes: Story = {
  render: () => ({
    components: { Input },
    setup() {
      const base = ref('');
      const sm = ref('');
      const xs = ref('');
      return { base, sm, xs };
    },
    template: `
      <div class="space-y-4">
        <Input v-model="base" size="base" placeholder="Base size" />
        <Input v-model="sm" size="sm" placeholder="Small size" />
        <Input v-model="xs" size="xs" placeholder="Extra small" />
      </div>
    `,
  }),
};

export const Variants: Story = {
  render: () => ({
    components: { Input },
    setup() {
      const default_ = ref('');
      const light = ref('');
      const ghost = ref('');
      return { default_, light, ghost };
    },
    template: `
      <div class="space-y-4">
        <Input v-model="default_" variant="default" placeholder="Default variant" />
        <Input v-model="light" variant="light" placeholder="Light variant" />
        <Input v-model="ghost" variant="ghost" placeholder="Ghost variant" />
      </div>
    `,
  }),
};

