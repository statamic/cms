import type { Meta, StoryObj } from '@storybook/vue3';
import { ref } from 'vue';
import Checkbox from '../Checkbox/Item.vue';
import CheckboxGroup from '../Checkbox/Group.vue';

const meta = {
  title: 'Components/Checkbox',
  component: Checkbox,
  tags: ['autodocs'],
  argTypes: {
    size: {
      control: 'select',
      options: ['sm', 'base'],
    },
    align: {
      control: 'select',
      options: ['start', 'center'],
    },
    label: { control: 'text' },
    description: { control: 'text' },
    disabled: { control: 'boolean' },
    readOnly: { control: 'boolean' },
  },
} satisfies Meta<typeof Checkbox>;

export default meta;
type Story = StoryObj<typeof meta>;

export const Default: Story = {
  render: (args) => ({
    components: { Checkbox },
    setup() {
      const checked = ref(false);
      return { args, checked };
    },
    template: '<Checkbox v-bind="args" v-model="checked" label="Accept terms" />',
  }),
};

export const Checked: Story = {
  render: (args) => ({
    components: { Checkbox },
    setup() {
      const checked = ref(true);
      return { args, checked };
    },
    template: '<Checkbox v-bind="args" v-model="checked" label="Already accepted" />',
  }),
};

export const WithDescription: Story = {
  render: (args) => ({
    components: { Checkbox },
    setup() {
      const checked = ref(false);
      return { args, checked };
    },
    template: '<Checkbox v-bind="args" v-model="checked" label="Enable notifications" description="Receive email updates about new features and updates" />',
  }),
};

export const Disabled: Story = {
  render: (args) => ({
    components: { Checkbox },
    setup() {
      const checked = ref(false);
      return { args, checked };
    },
    template: '<Checkbox v-bind="args" v-model="checked" label="Disabled checkbox" :disabled="true" />',
  }),
};

export const ReadOnly: Story = {
  render: (args) => ({
    components: { Checkbox },
    setup() {
      const checked = ref(true);
      return { args, checked };
    },
    template: '<Checkbox v-bind="args" v-model="checked" label="Read only (checked)" :read-only="true" />',
  }),
};

export const Small: Story = {
  render: (args) => ({
    components: { Checkbox },
    setup() {
      const checked = ref(false);
      return { args, checked };
    },
    template: '<Checkbox v-bind="args" v-model="checked" label="Small checkbox" size="sm" />',
  }),
};

export const Group: Story = {
  render: () => ({
    components: { CheckboxGroup, Checkbox },
    setup() {
      const selected = ref(['option1']);
      return { selected };
    },
    template: `
      <CheckboxGroup v-model="selected">
        <Checkbox value="option1" label="Option 1" description="First option" />
        <Checkbox value="option2" label="Option 2" description="Second option" />
        <Checkbox value="option3" label="Option 3" description="Third option" />
      </CheckboxGroup>
    `,
  }),
};

export const Solo: Story = {
  render: (args) => ({
    components: { Checkbox },
    setup() {
      const checked = ref(false);
      return { args, checked };
    },
    template: '<Checkbox v-bind="args" v-model="checked" :solo="true" label="Solo checkbox" />',
  }),
};

