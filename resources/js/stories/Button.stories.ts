import type { Meta, StoryObj } from '@storybook/vue3';
import Button from '@ui/Button/Button.vue';

const meta = {
  title: 'Components/Button',
  component: Button,
  argTypes: {
    variant: {
      control: 'select',
      options: ['default', 'primary', 'danger', 'filled', 'ghost', 'ghost-pressed', 'subtle', 'pressed'],
    },
    size: {
      control: 'select',
      options: ['lg', 'base', 'sm', 'xs'],
    },
    text: { control: 'text' },
    icon: { control: 'text' },
    iconAppend: { control: 'text' },
    loading: { control: 'boolean' },
    disabled: { control: 'boolean' },
    round: { control: 'boolean' },
    iconOnly: { control: 'boolean' },
  },
} satisfies Meta<typeof Button>;

export default meta;
type Story = StoryObj<typeof meta>;

export const Default: Story = {
  args: {
    text: 'Button',
  },
};

export const Primary: Story = {
  args: {
    text: 'Primary Button',
    variant: 'primary',
  },
};

export const Danger: Story = {
  args: {
    text: 'Delete',
    variant: 'danger',
  },
};

export const Ghost: Story = {
  args: {
    text: 'Ghost Button',
    variant: 'ghost',
  },
};

export const Subtle: Story = {
  args: {
    text: 'Subtle Button',
    variant: 'subtle',
  },
};

export const WithIcon: Story = {
  args: {
    text: 'Save',
    icon: 'check',
  },
};

export const WithAppendedIcon: Story = {
  args: {
    text: 'Continue',
    iconAppend: 'chevron-right',
  },
};

export const IconOnly: Story = {
  args: {
    icon: 'settings',
    iconOnly: true,
  },
};

export const Loading: Story = {
  args: {
    text: 'Loading',
    loading: true,
  },
};

export const Disabled: Story = {
  args: {
    text: 'Disabled',
    disabled: true,
  },
};

export const Sizes: Story = {
  render: () => ({
    components: { Button },
    template: `
      <div class="flex items-center gap-4">
        <Button size="lg" text="Large" />
        <Button size="base" text="Base" />
        <Button size="sm" text="Small" />
        <Button size="xs" text="Tiny" />
      </div>
    `,
  }),
};

export const Variants: Story = {
  render: () => ({
    components: { Button },
    template: `
      <div class="flex flex-wrap gap-2">
        <Button variant="default" text="Default" />
        <Button variant="primary" text="Primary" />
        <Button variant="danger" text="Danger" />
        <Button variant="filled" text="Filled" />
        <Button variant="ghost" text="Ghost" />
        <Button variant="subtle" text="Subtle" />
        <Button variant="pressed" text="Pressed" />
      </div>
    `,
  }),
};

export const Round: Story = {
  args: {
    icon: 'plus',
    iconOnly: true,
    round: true,
  },
};

