import type { Meta, StoryObj } from '@storybook/vue3';
import { ref } from 'vue';
import Slider from '@statamic/ui/Slider/Slider.vue';

const meta = {
  title: 'Components/Slider',
  component: Slider,
  tags: ['autodocs'],
  argTypes: {
    size: {
      control: 'select',
      options: ['sm', 'base'],
    },
    variant: {
      control: 'select',
      options: ['default'],
    },
    min: { control: 'number' },
    max: { control: 'number' },
    step: { control: 'number' },
  },
} satisfies Meta<typeof Slider>;

export default meta;
type Story = StoryObj<typeof meta>;

export const Default: Story = {
  render: (args) => ({
    components: { Slider },
    setup() {
      const value = ref(50);
      return { args, value };
    },
    template: `
      <div class="space-y-2">
        <Slider v-bind="args" v-model="value" />
        <div class="text-sm text-gray-600">Value: {{ value }}</div>
      </div>
    `,
  }),
};

export const WithLabel: Story = {
  render: (args) => ({
    components: { Slider },
    setup() {
      const volume = ref(75);
      return { args, volume };
    },
    template: `
      <div class="space-y-2">
        <label class="block text-sm font-medium">Volume: {{ volume }}%</label>
        <Slider v-bind="args" v-model="volume" :min="0" :max="100" label="Volume control" />
      </div>
    `,
  }),
};

export const CustomRange: Story = {
  render: (args) => ({
    components: { Slider },
    setup() {
      const price = ref(500);
      return { args, price };
    },
    template: `
      <div class="space-y-2">
        <label class="block text-sm font-medium">Price: ${{ price }}</label>
        <Slider v-bind="args" v-model="price" :min="0" :max="1000" :step="50" />
      </div>
    `,
  }),
};

export const Small: Story = {
  render: (args) => ({
    components: { Slider },
    setup() {
      const value = ref(30);
      return { args, value };
    },
    template: `
      <div class="space-y-2">
        <Slider v-bind="args" v-model="value" size="sm" />
        <div class="text-xs text-gray-600">Small slider: {{ value }}</div>
      </div>
    `,
  }),
};

export const WithSteps: Story = {
  render: (args) => ({
    components: { Slider },
    setup() {
      const rating = ref(3);
      return { args, rating };
    },
    template: `
      <div class="space-y-2">
        <label class="block text-sm font-medium">Rating: {{ rating }} stars</label>
        <Slider v-bind="args" v-model="rating" :min="1" :max="5" :step="1" />
      </div>
    `,
  }),
};

export const MultipleSliders: Story = {
  render: () => ({
    components: { Slider },
    setup() {
      const red = ref(255);
      const green = ref(128);
      const blue = ref(0);
      return { red, green, blue };
    },
    template: `
      <div class="space-y-4">
        <div
          class="h-24 rounded-lg mb-4"
          :style="{ backgroundColor: \`rgb(\${red}, \${green}, \${blue})\` }"
        ></div>
        <div class="space-y-2">
          <label class="block text-sm font-medium">Red: {{ red }}</label>
          <Slider v-model="red" :min="0" :max="255" />
        </div>
        <div class="space-y-2">
          <label class="block text-sm font-medium">Green: {{ green }}</label>
          <Slider v-model="green" :min="0" :max="255" />
        </div>
        <div class="space-y-2">
          <label class="block text-sm font-medium">Blue: {{ blue }}</label>
          <Slider v-model="blue" :min="0" :max="255" />
        </div>
      </div>
    `,
  }),
};

