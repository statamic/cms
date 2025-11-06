import type { Meta, StoryObj } from '@storybook/vue3';
import Card from '../Card/Card.vue';
import Button from '../Button/Button.vue';

const meta = {
  title: 'Components/Card',
  component: Card,
  tags: ['autodocs'],
  argTypes: {
    variant: {
      control: 'select',
      options: ['default', 'flat'],
    },
    inset: { control: 'boolean' },
  },
} satisfies Meta<typeof Card>;

export default meta;
type Story = StoryObj<typeof meta>;

export const Default: Story = {
  render: (args) => ({
    components: { Card },
    setup() {
      return { args };
    },
    template: `
      <Card v-bind="args">
        <h3 class="text-lg font-semibold">Card Title</h3>
        <p class="text-sm text-gray-600 dark:text-gray-400">This is a basic card with some content inside.</p>
      </Card>
    `,
  }),
};

export const WithActions: Story = {
  render: (args) => ({
    components: { Card, Button },
    setup() {
      return { args };
    },
    template: `
      <Card v-bind="args">
        <h3 class="text-lg font-semibold mb-2">Project Update</h3>
        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
          Your project has been successfully deployed to production.
        </p>
        <div class="flex gap-2">
          <Button text="View Details" variant="primary" size="sm" />
          <Button text="Dismiss" variant="ghost" size="sm" />
        </div>
      </Card>
    `,
  }),
};

export const Flat: Story = {
  render: (args) => ({
    components: { Card },
    setup() {
      return { args };
    },
    template: `
      <Card v-bind="args" variant="flat">
        <h3 class="text-lg font-semibold">Flat Card</h3>
        <p class="text-sm text-gray-600 dark:text-gray-400">A card without shadow.</p>
      </Card>
    `,
  }),
};

export const Inset: Story = {
  render: (args) => ({
    components: { Card },
    setup() {
      return { args };
    },
    template: `
      <Card v-bind="args" :inset="true">
        <div class="p-4">
          <h3 class="text-lg font-semibold">Inset Card</h3>
          <p class="text-sm text-gray-600 dark:text-gray-400">This card has no internal padding by default.</p>
        </div>
      </Card>
    `,
  }),
};

export const Grid: Story = {
  render: () => ({
    components: { Card, Button },
    template: `
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <Card>
          <h3 class="text-lg font-semibold">Feature 1</h3>
          <p class="text-sm text-gray-600 dark:text-gray-400">Description of the first feature.</p>
        </Card>
        <Card>
          <h3 class="text-lg font-semibold">Feature 2</h3>
          <p class="text-sm text-gray-600 dark:text-gray-400">Description of the second feature.</p>
        </Card>
        <Card>
          <h3 class="text-lg font-semibold">Feature 3</h3>
          <p class="text-sm text-gray-600 dark:text-gray-400">Description of the third feature.</p>
        </Card>
      </div>
    `,
  }),
};

