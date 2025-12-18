import type {Meta, StoryObj} from '@storybook/vue3';
import {Button, Dropdown, DropdownFooter, DropdownHeader, DropdownItem, DropdownMenu, DropdownSeparator} from '@ui';

const meta = {
    title: 'Components/Dropdown',
    component: Dropdown,
    subcomponents: {
        DropdownMenu,
        DropdownItem,
        DropdownHeader,
        DropdownFooter,
        DropdownSeparator,
    },
    argTypes: {
        // align: {
        //     control: 'select',
        //     options: ['start', 'center', 'end'],
        //     description: 'The preferred alignment against the trigger. May change when collisions occur. <br><br> Options: `start`, `center`, `end`',
        // },
        // offset: {
        //     control: 'number',
        //     description: 'The distance in pixels from the trigger.',
        // },
        // side: {
        //     control: 'select',
        //     options: ['top', 'bottom', 'left', 'right'],
        //     description: 'The preferred side of the trigger to render against when open. <br><br> Options: `top`, `bottom`, `left`, `right`',
        // },
    },
} satisfies Meta<typeof Dropdown>;

export default meta;
type Story = StoryObj<typeof meta>;

const defaultCode = `
<Dropdown>
    <template #trigger>
        <Button text="Do a Action" icon-append="ui/chevron-vertical" class="[&_svg]:size-2" />
    </template>
    <DropdownMenu>
        <DropdownItem text="Bake a food" />
        <DropdownItem text="Write that book" />
        <DropdownItem text="Eat this meal" />
        <DropdownItem text="Lie about larceny" />
        <DropdownItem text="Save some bird" />
    </DropdownMenu>
</Dropdown>
`;

export const _DocsIntro: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: defaultCode }
        }
    },
    render: () => ({
        components: { Dropdown, DropdownMenu, DropdownItem, Button },
        template: defaultCode,
    }),
};

const iconsCode = `
<Dropdown>
    <template #trigger>
        <Button text="Go To..." icon-append="ui/chevron-vertical" class="[&_svg]:size-2" />
    </template>
    <DropdownMenu>
        <DropdownItem text="Assets" icon="assets" />
        <DropdownItem text="Collections" icon="collections" />
        <DropdownItem text="Globals" icon="globals" />
        <DropdownItem text="Navigation" icon="navigation" />
        <DropdownSeparator />
        <DropdownItem text="Taxonomies" icon="taxonomies" />
    </DropdownMenu>
</Dropdown>
`;

export const _Icons: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: iconsCode }
        }
    },
    render: () => ({
        components: { Dropdown, DropdownMenu, DropdownItem, DropdownSeparator, Button },
        template: iconsCode,
    }),
};

const headersFootersCode = `
<Dropdown>
    <template #trigger>
        <Button text="My Account" icon-append="ui/chevron-vertical" class="[&_svg]:size-2" />
    </template>
    <DropdownHeader text="My Account" icon="avatar" />
    <DropdownMenu>
        <DropdownItem text="Photos" icon="assets" />
        <DropdownItem text="Email" icon="mail" />
        <DropdownItem text="Sales" icon="taxonomies" />
    </DropdownMenu>
    <DropdownFooter text="Logout" icon="arrow-right" />
</Dropdown>
`;

export const _HeadersFooters: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: headersFootersCode }
        }
    },
    render: () => ({
        components: { Dropdown, DropdownMenu, DropdownItem, DropdownHeader, DropdownFooter, Button },
        template: headersFootersCode,
    }),
};

const destructiveCode = `
<Dropdown>
    <template #trigger>
        <Button text="Show List of Actions" />
    </template>
    <DropdownMenu>
        <DropdownItem text="Do a Nothing" icon="sun" />
        <DropdownItem text="Delete a Something" variant="destructive" icon="trash" />
    </DropdownMenu>
</Dropdown>
`;

export const _Destructive: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: destructiveCode }
        }
    },
    render: () => ({
        components: { Dropdown, DropdownMenu, DropdownItem, Button },
        template: destructiveCode,
    }),
};

const disabledCode = `
<Dropdown>
    <template #trigger>
        <Button text="Go To..." icon-append="ui/chevron-vertical" class="[&_svg]:size-2" />
    </template>
    <DropdownMenu>
        <DropdownItem text="Collections" icon="collections" />
        <DropdownItem text="Taxonomies" icon="taxonomies" disabled />
        <DropdownItem text="Globals" icon="globals" />
    </DropdownMenu>
</Dropdown>
`;

export const _Disabled: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: disabledCode }
        }
    },
    render: () => ({
        components: { Dropdown, DropdownMenu, DropdownItem, Button },
        template: disabledCode,
    }),
};
