import type { Meta, StoryObj } from '@storybook/vue3';
import { ref } from 'vue';
import Textarea from '../Textarea.vue';

const meta = {
  title: 'Components/Textarea',
  component: Textarea,
  tags: ['autodocs'],
  argTypes: {
    resize: {
      control: 'select',
      options: ['both', 'horizontal', 'vertical', 'none'],
    },
    rows: { control: 'number' },
    disabled: { control: 'boolean' },
    readOnly: { control: 'boolean' },
    elastic: { control: 'boolean' },
  },
} satisfies Meta<typeof Textarea>;

export default meta;
type Story = StoryObj<typeof meta>;

export const Default: Story = {
  render: (args) => ({
    components: { Textarea },
    setup() {
      const value = ref('');
      return { args, value };
    },
    template: '<Textarea v-bind="args" v-model="value" placeholder="Enter your text here..." />',
  }),
};

export const WithValue: Story = {
  render: (args) => ({
    components: { Textarea },
    setup() {
      const value = ref('This is some pre-filled text in the textarea.\n\nYou can edit it as needed.');
      return { args, value };
    },
    template: '<Textarea v-bind="args" v-model="value" />',
  }),
};

export const WithLimit: Story = {
  render: (args) => ({
    components: { Textarea },
    setup() {
      const value = ref('This textarea has a character limit.');
      return { args, value };
    },
    template: '<Textarea v-bind="args" v-model="value" :limit="200" placeholder="Max 200 characters" />',
  }),
};

export const Disabled: Story = {
  render: (args) => ({
    components: { Textarea },
    setup() {
      const value = ref('This textarea is disabled and cannot be edited.');
      return { args, value };
    },
    template: '<Textarea v-bind="args" v-model="value" :disabled="true" />',
  }),
};

export const ReadOnly: Story = {
  render: (args) => ({
    components: { Textarea },
    setup() {
      const value = ref('This textarea is read-only. You can select and copy the text, but not edit it.');
      return { args, value };
    },
    template: '<Textarea v-bind="args" v-model="value" :read-only="true" />',
  }),
};

export const CustomRows: Story = {
  render: (args) => ({
    components: { Textarea },
    setup() {
      const value = ref('');
      return { args, value };
    },
    template: '<Textarea v-bind="args" v-model="value" :rows="10" placeholder="This textarea has 10 rows" />',
  }),
};

export const ResizeOptions: Story = {
  render: () => ({
    components: { Textarea },
    setup() {
      const vertical = ref('');
      const horizontal = ref('');
      const both = ref('');
      const none = ref('');
      return { vertical, horizontal, both, none };
    },
    template: `
      <div class="space-y-4">
        <div>
          <label class="block text-sm font-medium mb-1">Vertical resize (default)</label>
          <Textarea v-model="vertical" resize="vertical" placeholder="Resize vertically only" />
        </div>
        <div>
          <label class="block text-sm font-medium mb-1">Horizontal resize</label>
          <Textarea v-model="horizontal" resize="horizontal" placeholder="Resize horizontally only" />
        </div>
        <div>
          <label class="block text-sm font-medium mb-1">Both directions</label>
          <Textarea v-model="both" resize="both" placeholder="Resize in both directions" />
        </div>
        <div>
          <label class="block text-sm font-medium mb-1">No resize</label>
          <Textarea v-model="none" resize="none" placeholder="Cannot be resized" />
        </div>
      </div>
    `,
  }),
};

export const Elastic: Story = {
  render: (args) => ({
    components: { Textarea },
    setup() {
      const value = ref('This textarea automatically grows as you type more content.\n\nTry adding more lines!');
      return { args, value };
    },
    template: '<Textarea v-bind="args" v-model="value" :elastic="true" :rows="2" placeholder="Type to see it grow..." />',
  }),
};

