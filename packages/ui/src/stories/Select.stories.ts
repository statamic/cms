import type { Meta, StoryObj } from '@storybook/vue3';
import { ref } from 'vue';
import Select from '../Select/Select.vue';

const meta = {
  title: 'Components/Select',
  component: Select,
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
    placeholder: { control: 'text' },
    disabled: { control: 'boolean' },
    readOnly: { control: 'boolean' },
    clearable: { control: 'boolean' },
  },
} satisfies Meta<typeof Select>;

export default meta;
type Story = StoryObj<typeof meta>;

const sampleOptions = [
  { value: 'apple', label: 'Apple' },
  { value: 'banana', label: 'Banana' },
  { value: 'cherry', label: 'Cherry' },
  { value: 'date', label: 'Date' },
  { value: 'elderberry', label: 'Elderberry' },
];

export const Default: Story = {
  render: (args) => ({
    components: { Select },
    setup() {
      const selected = ref(null);
      return { args, selected, sampleOptions };
    },
    template: '<Select v-bind="args" v-model="selected" :options="sampleOptions" placeholder="Select a fruit..." />',
  }),
};

export const WithValue: Story = {
  render: (args) => ({
    components: { Select },
    setup() {
      const selected = ref('banana');
      return { args, selected, sampleOptions };
    },
    template: '<Select v-bind="args" v-model="selected" :options="sampleOptions" />',
  }),
};

export const Clearable: Story = {
  render: (args) => ({
    components: { Select },
    setup() {
      const selected = ref('cherry');
      return { args, selected, sampleOptions };
    },
    template: '<Select v-bind="args" v-model="selected" :options="sampleOptions" :clearable="true" />',
  }),
};

export const WithIcon: Story = {
  render: (args) => ({
    components: { Select },
    setup() {
      const selected = ref(null);
      return { args, selected, sampleOptions };
    },
    template: '<Select v-bind="args" v-model="selected" :options="sampleOptions" icon="tag" placeholder="Select a tag..." />',
  }),
};

export const Disabled: Story = {
  render: (args) => ({
    components: { Select },
    setup() {
      const selected = ref('apple');
      return { args, selected, sampleOptions };
    },
    template: '<Select v-bind="args" v-model="selected" :options="sampleOptions" :disabled="true" />',
  }),
};

export const Sizes: Story = {
  render: () => ({
    components: { Select },
    setup() {
      const base = ref(null);
      const sm = ref(null);
      const xs = ref(null);
      return { base, sm, xs, sampleOptions };
    },
    template: `
      <div class="space-y-4">
        <Select v-model="base" size="base" :options="sampleOptions" placeholder="Base size" />
        <Select v-model="sm" size="sm" :options="sampleOptions" placeholder="Small size" />
        <Select v-model="xs" size="xs" :options="sampleOptions" placeholder="Extra small" />
      </div>
    `,
  }),
};

const countryOptions = [
  { value: 'us', label: 'United States' },
  { value: 'uk', label: 'United Kingdom' },
  { value: 'ca', label: 'Canada' },
  { value: 'au', label: 'Australia' },
  { value: 'de', label: 'Germany' },
  { value: 'fr', label: 'France' },
  { value: 'jp', label: 'Japan' },
];

export const Countries: Story = {
  render: () => ({
    components: { Select },
    setup() {
      const selected = ref(null);
      return { selected, countryOptions };
    },
    template: '<Select v-model="selected" :options="countryOptions" icon="globe" placeholder="Select a country..." />',
  }),
};

