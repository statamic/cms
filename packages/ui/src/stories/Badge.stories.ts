import type { Meta, StoryObj } from '@storybook/vue3';
import Badge from '../Badge.vue';

const meta = {
  title: 'Components/Badge',
  component: Badge,
  tags: ['autodocs'],
  argTypes: {
    color: {
      control: 'select',
      options: ['default', 'blue', 'green', 'red', 'yellow', 'purple', 'pink', 'indigo', 'cyan', 'teal', 'orange', 'amber', 'lime', 'emerald', 'sky', 'violet', 'fuchsia', 'rose', 'black', 'white'],
    },
    size: {
      control: 'select',
      options: ['sm', 'default', 'lg'],
    },
    text: { control: 'text' },
    icon: { control: 'text' },
    iconAppend: { control: 'text' },
    pill: { control: 'boolean' },
  },
} satisfies Meta<typeof Badge>;

export default meta;
type Story = StoryObj<typeof meta>;

export const Default: Story = {
  args: {
    text: 'Badge',
  },
};

export const WithIcon: Story = {
  args: {
    text: 'Featured',
    icon: 'star',
    color: 'yellow',
  },
};

export const WithAppendedIcon: Story = {
  args: {
    text: 'Published',
    iconAppend: 'check',
    color: 'green',
  },
};

export const Pill: Story = {
  args: {
    text: 'Pill Badge',
    pill: true,
    color: 'blue',
  },
};

export const WithPrepend: Story = {
  args: {
    text: 'Items',
    prepend: '5',
    color: 'purple',
  },
};

export const WithAppend: Story = {
  args: {
    text: 'Score',
    append: '100',
    color: 'emerald',
  },
};

export const Sizes: Story = {
  render: () => ({
    components: { Badge },
    template: `
      <div class="flex items-center gap-2">
        <Badge size="sm" text="Small" />
        <Badge size="default" text="Default" />
        <Badge size="lg" text="Large" />
      </div>
    `,
  }),
};

export const Colors: Story = {
  render: () => ({
    components: { Badge },
    template: `
      <div class="flex flex-wrap gap-2">
        <Badge color="default" text="Default" />
        <Badge color="blue" text="Blue" />
        <Badge color="green" text="Green" />
        <Badge color="red" text="Red" />
        <Badge color="yellow" text="Yellow" />
        <Badge color="purple" text="Purple" />
        <Badge color="pink" text="Pink" />
        <Badge color="indigo" text="Indigo" />
        <Badge color="cyan" text="Cyan" />
        <Badge color="teal" text="Teal" />
        <Badge color="orange" text="Orange" />
        <Badge color="amber" text="Amber" />
        <Badge color="lime" text="Lime" />
        <Badge color="emerald" text="Emerald" />
        <Badge color="sky" text="Sky" />
        <Badge color="violet" text="Violet" />
        <Badge color="fuchsia" text="Fuchsia" />
        <Badge color="rose" text="Rose" />
      </div>
    `,
  }),
};

export const StatusIndicators: Story = {
  render: () => ({
    components: { Badge },
    template: `
      <div class="flex flex-wrap gap-2">
        <Badge color="green" text="Active" icon="check-circle" />
        <Badge color="yellow" text="Pending" icon="clock" />
        <Badge color="red" text="Error" icon="x-circle" />
        <Badge color="blue" text="Draft" icon="edit" />
        <Badge color="purple" text="Scheduled" icon="calendar" />
      </div>
    `,
  }),
};

