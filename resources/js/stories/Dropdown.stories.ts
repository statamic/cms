import type { Meta, StoryObj } from '@storybook/vue3';
import Dropdown from '@statamic/ui/Dropdown/Dropdown.vue';
import DropdownMenu from '@statamic/ui/Dropdown/Menu.vue';
import DropdownItem from '@statamic/ui/Dropdown/Item.vue';
import DropdownLabel from '@statamic/ui/Dropdown/Label.vue';
import DropdownSeparator from '@statamic/ui/Dropdown/Separator.vue';
import Button from '@statamic/ui/Button/Button.vue';

const meta = {
  title: 'Components/Dropdown',
  component: Dropdown,
  tags: ['autodocs'],
  argTypes: {
    align: {
      control: 'select',
      options: ['start', 'center', 'end'],
    },
    side: {
      control: 'select',
      options: ['top', 'right', 'bottom', 'left'],
    },
  },
} satisfies Meta<typeof Dropdown>;

export default meta;
type Story = StoryObj<typeof meta>;

export const Default: Story = {
  render: (args) => ({
    components: { Dropdown, DropdownMenu, DropdownItem },
    setup() {
      const handleClick = (action: string) => {
        console.log(`${action} clicked`);
      };
      return { args, handleClick };
    },
    template: `
      <Dropdown v-bind="args">
        <DropdownMenu>
          <DropdownItem @click="handleClick('Edit')">Edit</DropdownItem>
          <DropdownItem @click="handleClick('Duplicate')">Duplicate</DropdownItem>
          <DropdownItem @click="handleClick('Delete')">Delete</DropdownItem>
        </DropdownMenu>
      </Dropdown>
    `,
  }),
};

export const WithCustomTrigger: Story = {
  render: (args) => ({
    components: { Dropdown, DropdownMenu, DropdownItem, Button },
    setup() {
      return { args };
    },
    template: `
      <Dropdown v-bind="args">
        <template #trigger>
          <Button text="Actions" iconAppend="chevron-down" />
        </template>
        <DropdownMenu>
          <DropdownItem>Export</DropdownItem>
          <DropdownItem>Import</DropdownItem>
          <DropdownItem>Share</DropdownItem>
        </DropdownMenu>
      </Dropdown>
    `,
  }),
};

export const WithSections: Story = {
  render: (args) => ({
    components: { Dropdown, DropdownMenu, DropdownItem, DropdownLabel, DropdownSeparator },
    setup() {
      return { args };
    },
    template: `
      <Dropdown v-bind="args">
        <DropdownMenu>
          <DropdownLabel>Account</DropdownLabel>
          <DropdownItem>Profile</DropdownItem>
          <DropdownItem>Settings</DropdownItem>
          <DropdownSeparator />
          <DropdownLabel>Danger Zone</DropdownLabel>
          <DropdownItem>Delete Account</DropdownItem>
        </DropdownMenu>
      </Dropdown>
    `,
  }),
};

export const WithIcons: Story = {
  render: (args) => ({
    components: { Dropdown, DropdownMenu, DropdownItem },
    setup() {
      return { args };
    },
    template: `
      <Dropdown v-bind="args">
        <DropdownMenu>
          <DropdownItem>
            <span class="flex items-center gap-2">
              <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
              Edit
            </span>
          </DropdownItem>
          <DropdownItem>
            <span class="flex items-center gap-2">
              <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" /></svg>
              Copy
            </span>
          </DropdownItem>
          <DropdownItem>
            <span class="flex items-center gap-2 text-red-600">
              <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
              Delete
            </span>
          </DropdownItem>
        </DropdownMenu>
      </Dropdown>
    `,
  }),
};

export const Alignment: Story = {
  render: () => ({
    components: { Dropdown, DropdownMenu, DropdownItem, Button },
    template: `
      <div class="flex gap-4 justify-center">
        <Dropdown align="start">
          <template #trigger>
            <Button text="Align Start" size="sm" />
          </template>
          <DropdownMenu>
            <DropdownItem>Option 1</DropdownItem>
            <DropdownItem>Option 2</DropdownItem>
          </DropdownMenu>
        </Dropdown>

        <Dropdown align="center">
          <template #trigger>
            <Button text="Align Center" size="sm" />
          </template>
          <DropdownMenu>
            <DropdownItem>Option 1</DropdownItem>
            <DropdownItem>Option 2</DropdownItem>
          </DropdownMenu>
        </Dropdown>

        <Dropdown align="end">
          <template #trigger>
            <Button text="Align End" size="sm" />
          </template>
          <DropdownMenu>
            <DropdownItem>Option 1</DropdownItem>
            <DropdownItem>Option 2</DropdownItem>
          </DropdownMenu>
        </Dropdown>
      </div>
    `,
  }),
};

