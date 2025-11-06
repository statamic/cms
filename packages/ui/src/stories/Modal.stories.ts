import type { Meta, StoryObj } from '@storybook/vue3';
import { ref } from 'vue';
import Modal from '../Modal/Modal.vue';
import Button from '../Button/Button.vue';

const meta = {
  title: 'Components/Modal',
  component: Modal,
  tags: ['autodocs'],
  argTypes: {
    title: { control: 'text' },
    icon: { control: 'text' },
    dismissible: { control: 'boolean' },
    blur: { control: 'boolean' },
  },
} satisfies Meta<typeof Modal>;

export default meta;
type Story = StoryObj<typeof meta>;

export const Default: Story = {
  render: (args) => ({
    components: { Modal, Button },
    setup() {
      return { args };
    },
    template: `
      <Modal v-bind="args" title="Modal Title">
        <template #trigger>
          <Button text="Open Modal" />
        </template>
        <p>This is the modal content. You can put anything here.</p>
        <p>Click outside or press Escape to close.</p>
      </Modal>
    `,
  }),
};

export const WithIcon: Story = {
  render: (args) => ({
    components: { Modal, Button },
    setup() {
      return { args };
    },
    template: `
      <Modal v-bind="args" title="Confirmation" icon="check-circle">
        <template #trigger>
          <Button text="Show Confirmation" variant="primary" />
        </template>
        <p>Are you sure you want to proceed with this action?</p>
      </Modal>
    `,
  }),
};

export const WithFooter: Story = {
  render: (args) => ({
    components: { Modal, Button },
    setup() {
      return { args };
    },
    template: `
      <Modal v-bind="args" title="Confirm Action">
        <template #trigger>
          <Button text="Open with Footer" />
        </template>
        <p>This modal has custom footer buttons.</p>
        <template #footer>
          <div class="flex gap-2 justify-end mt-2">
            <Button text="Cancel" variant="ghost" />
            <Button text="Confirm" variant="primary" />
          </div>
        </template>
      </Modal>
    `,
  }),
};

export const NotDismissible: Story = {
  render: (args) => ({
    components: { Modal, Button },
    setup() {
      const open = ref(false);
      return { args, open };
    },
    template: `
      <Modal v-bind="args" v-model:open="open" title="Required Action" :dismissible="false">
        <template #trigger>
          <Button text="Open (Not Dismissible)" />
        </template>
        <p>This modal cannot be dismissed by clicking outside or pressing Escape.</p>
        <p>You must use the button below to close it.</p>
        <div class="mt-4">
          <Button text="Close Modal" variant="primary" @click="open = false" />
        </div>
      </Modal>
    `,
  }),
};

export const LongContent: Story = {
  render: (args) => ({
    components: { Modal, Button },
    setup() {
      return { args };
    },
    template: `
      <Modal v-bind="args" title="Terms and Conditions">
        <template #trigger>
          <Button text="View Terms" />
        </template>
        <div class="space-y-4">
          <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>
          <p>Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>
          <p>Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur.</p>
          <p>Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p>
          <p>Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium.</p>
          <p>Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur magni dolores.</p>
          <p>At vero eos et accusamus et iusto odio dignissimos ducimus qui blanditiis praesentium voluptatum deleniti atque corrupti.</p>
        </div>
      </Modal>
    `,
  }),
};

export const ControlledState: Story = {
  render: (args) => ({
    components: { Modal, Button },
    setup() {
      const open = ref(false);
      return { args, open };
    },
    template: `
      <div>
        <Button text="Open Controlled Modal" @click="open = true" />
        <Modal v-bind="args" v-model:open="open" title="Controlled Modal">
          <p>This modal's open state is controlled by a parent component.</p>
          <div class="flex gap-2 mt-4">
            <Button text="Close" @click="open = false" />
            <Button text="Toggle" variant="ghost" @click="open = !open" />
          </div>
        </Modal>
      </div>
    `,
  }),
};

