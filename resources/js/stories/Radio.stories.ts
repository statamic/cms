import type { Meta, StoryObj } from '@storybook/vue3';
import { ref } from 'vue';
import Radio from '@statamic/ui/Radio/Item.vue';
import RadioGroup from '@statamic/ui/Radio/Group.vue';

const meta = {
  title: 'Components/Radio',
  component: Radio,
  tags: ['autodocs'],
  argTypes: {
    label: { control: 'text' },
    description: { control: 'text' },
    disabled: { control: 'boolean' },
  },
} satisfies Meta<typeof Radio>;

export default meta;
type Story = StoryObj<typeof meta>;

export const Default: Story = {
  render: () => ({
    components: { RadioGroup, Radio },
    setup() {
      const selected = ref('option1');
      return { selected };
    },
    template: `
      <RadioGroup v-model="selected">
        <Radio value="option1" label="Option 1" />
        <Radio value="option2" label="Option 2" />
        <Radio value="option3" label="Option 3" />
      </RadioGroup>
    `,
  }),
};

export const WithDescriptions: Story = {
  render: () => ({
    components: { RadioGroup, Radio },
    setup() {
      const selected = ref('basic');
      return { selected };
    },
    template: `
      <RadioGroup v-model="selected">
        <Radio value="basic" label="Basic Plan" description="Perfect for individuals and small teams" />
        <Radio value="pro" label="Pro Plan" description="Advanced features for growing businesses" />
        <Radio value="enterprise" label="Enterprise Plan" description="Custom solutions for large organizations" />
      </RadioGroup>
    `,
  }),
};

export const WithDisabled: Story = {
  render: () => ({
    components: { RadioGroup, Radio },
    setup() {
      const selected = ref('available');
      return { selected };
    },
    template: `
      <RadioGroup v-model="selected">
        <Radio value="available" label="Available option" />
        <Radio value="disabled" label="Disabled option" :disabled="true" />
        <Radio value="another" label="Another available option" />
      </RadioGroup>
    `,
  }),
};

export const Horizontal: Story = {
  render: () => ({
    components: { RadioGroup, Radio },
    setup() {
      const selected = ref('yes');
      return { selected };
    },
    template: `
      <RadioGroup v-model="selected" class="flex gap-6">
        <Radio value="yes" label="Yes" />
        <Radio value="no" label="No" />
        <Radio value="maybe" label="Maybe" />
      </RadioGroup>
    `,
  }),
};

