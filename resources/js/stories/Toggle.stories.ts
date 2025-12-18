import type {Meta, StoryObj} from '@storybook/vue3';
import {ToggleGroup, ToggleItem} from '@ui';
import {ref} from 'vue';

const meta = {
    title: 'Components/Toggle',
    component: ToggleGroup,
    subcomponents: {
        ToggleItem,
    },
    argTypes: {
        modelValue: {
            control: 'text',
            description: 'The controlled value of the toggle group.',
            table: {
                type: { summary: 'string | string[]' }
            }
        },
        size: {
            control: 'select',
            description: 'Controls the size of the toggle items. <br><br> Options: `xs`, `sm`, `base`',
            options: ['xs', 'sm', 'base'],
        },
        variant: {
            control: 'select',
            description: 'Controls the appearance of the toggle items. <br><br> Options: `default`, `primary`, `filled`, `ghost`',
            options: ['default', 'primary', 'filled', 'ghost'],
        },
        multiple: {
            control: 'boolean',
            description: 'When `true`, multiple items can be selected',
        },
        required: { control: 'boolean' },
        'update:modelValue': {
            description: 'Event handler called when the selected options change.',
            table: {
                category: 'events',
                type: { summary: '(value: string | string[]) => void' }
            }
        }
    },
} satisfies Meta<typeof ToggleGroup>;

export default meta;
type Story = StoryObj<typeof meta>;

const introCode = `
<ToggleGroup v-model="selected">
    <ToggleItem value="grid" icon="view-grid" label="Grid" />
    <ToggleItem value="list" icon="view-list" label="List" />
    <ToggleItem value="table" icon="view-table" label="Table" />
</ToggleGroup>
`;

export const _DocsIntro: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: introCode },
        },
    },
    render: () => ({
        components: { ToggleGroup, ToggleItem },
        setup() {
            const selected = ref('list');
            return { selected };
        },
        template: introCode,
    }),
};

const defaultCode = `
<ToggleGroup v-model="selected">
    <ToggleItem value="option1" label="Option 1" />
    <ToggleItem value="option2" label="Option 2" />
    <ToggleItem value="option3" label="Option 3" />
</ToggleGroup>
`;

export const Default: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: defaultCode },
        },
    },
    render: () => ({
        components: { ToggleGroup, ToggleItem },
        setup() {
            const selected = ref('option1');
            return { selected };
        },
        template: defaultCode,
    }),
};

const variantsCode = `
<ToggleGroup v-model="selected" variant="default">
    <ToggleItem value="option1" label="Default" />
    <ToggleItem value="option2" label="Option 2" />
    <ToggleItem value="option3" label="Option 3" />
</ToggleGroup>

<ToggleGroup v-model="selected" variant="primary">
    <ToggleItem value="option1" label="Primary" />
    <ToggleItem value="option2" label="Option 2" />
    <ToggleItem value="option3" label="Option 3" />
</ToggleGroup>

<ToggleGroup v-model="selected" variant="filled">
    <ToggleItem value="option1" label="Filled" />
    <ToggleItem value="option2" label="Option 2" />
    <ToggleItem value="option3" label="Option 3" />
</ToggleGroup>

<ToggleGroup v-model="selected" variant="ghost">
    <ToggleItem value="option1" label="Ghost" />
    <ToggleItem value="option2" label="Option 2" />
    <ToggleItem value="option3" label="Option 3" />
</ToggleGroup>
`;

export const Variants: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: variantsCode },
        },
    },
    render: () => ({
        components: { ToggleGroup, ToggleItem },
        setup() {
            const selected1 = ref('option1');
            const selected2 = ref('option1');
            const selected3 = ref('option1');
            const selected4 = ref('option1');
            return { selected1, selected2, selected3, selected4 };
        },
        template: `
            <div class="space-y-4">
                <ToggleGroup v-model="selected1" variant="default">
                    <ToggleItem value="option1" label="Default" />
                    <ToggleItem value="option2" label="Option 2" />
                    <ToggleItem value="option3" label="Option 3" />
                </ToggleGroup>
                
                <ToggleGroup v-model="selected2" variant="primary">
                    <ToggleItem value="option1" label="Primary" />
                    <ToggleItem value="option2" label="Option 2" />
                    <ToggleItem value="option3" label="Option 3" />
                </ToggleGroup>
                
                <ToggleGroup v-model="selected3" variant="filled">
                    <ToggleItem value="option1" label="Filled" />
                    <ToggleItem value="option2" label="Option 2" />
                    <ToggleItem value="option3" label="Option 3" />
                </ToggleGroup>
                
                <ToggleGroup v-model="selected4" variant="ghost">
                    <ToggleItem value="option1" label="Ghost" />
                    <ToggleItem value="option2" label="Option 2" />
                    <ToggleItem value="option3" label="Option 3" />
                </ToggleGroup>
            </div>
        `,
    }),
};

const sizesCode = `
<ToggleGroup v-model="selected" size="base">
    <ToggleItem value="option1" label="Base" />
    <ToggleItem value="option2" label="Option 2" />
    <ToggleItem value="option3" label="Option 3" />
</ToggleGroup>

<ToggleGroup v-model="selected" size="sm">
    <ToggleItem value="option1" label="Small" />
    <ToggleItem value="option2" label="Option 2" />
    <ToggleItem value="option3" label="Option 3" />
</ToggleGroup>

<ToggleGroup v-model="selected" size="xs">
    <ToggleItem value="option1" label="Extra Small" />
    <ToggleItem value="option2" label="Option 2" />
    <ToggleItem value="option3" label="Option 3" />
</ToggleGroup>
`;

export const Sizes: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: sizesCode },
        },
    },
    render: () => ({
        components: { ToggleGroup, ToggleItem },
        setup() {
            const selected1 = ref('option1');
            const selected2 = ref('option1');
            const selected3 = ref('option1');
            return { selected1, selected2, selected3 };
        },
        template: `
            <div class="space-y-4">
                <ToggleGroup v-model="selected1" size="base">
                    <ToggleItem value="option1" label="Base" />
                    <ToggleItem value="option2" label="Option 2" />
                    <ToggleItem value="option3" label="Option 3" />
                </ToggleGroup>
                
                <ToggleGroup v-model="selected2" size="sm">
                    <ToggleItem value="option1" label="Small" />
                    <ToggleItem value="option2" label="Option 2" />
                    <ToggleItem value="option3" label="Option 3" />
                </ToggleGroup>
                
                <ToggleGroup v-model="selected3" size="xs">
                    <ToggleItem value="option1" label="Extra Small" />
                    <ToggleItem value="option2" label="Option 2" />
                    <ToggleItem value="option3" label="Option 3" />
                </ToggleGroup>
            </div>
        `,
    }),
};

const withIconsCode = `
<ToggleGroup v-model="selected">
    <ToggleItem value="tree" icon="navigation" label="Tree" />
    <ToggleItem value="list" icon="layout-list" label="List" />
    <ToggleItem value="calendar" icon="calendar" label="Calendar" />
</ToggleGroup>
`;

export const WithIcons: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: withIconsCode },
        },
    },
    render: () => ({
        components: { ToggleGroup, ToggleItem },
        setup() {
            const selected = ref('tree');
            return { selected };
        },
        template: withIconsCode,
    }),
};

const iconOnlyCode = `
<ToggleGroup v-model="selected">
    <ToggleItem value="bold" icon="text-bold" />
    <ToggleItem value="italic" icon="text-italic" />
    <ToggleItem value="underline" icon="text-underline" />
</ToggleGroup>
`;

export const IconOnly: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: iconOnlyCode },
        },
    },
    render: () => ({
        components: { ToggleGroup, ToggleItem },
        setup() {
            const selected = ref('bold');
            return { selected };
        },
        template: iconOnlyCode,
    }),
};

const multipleCode = `
<ToggleGroup v-model="selected" multiple>
    <ToggleItem value="bold" icon="text-bold" label="Bold" />
    <ToggleItem value="italic" icon="text-italic" label="Italic" />
    <ToggleItem value="underline" icon="text-underline" label="Underline" />
</ToggleGroup>
`;

export const Multiple: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { 
                code: `${multipleCode}
<div class="text-sm text-gray-600">Selected: {{ selected.join(', ') || 'none' }}</div>`
            },
        },
    },
    render: () => ({
        components: { ToggleGroup, ToggleItem },
        setup() {
            const selected = ref(['bold', 'italic']);
            return { selected };
        },
        template: `
            <div class="space-y-4">
                ${multipleCode}
                <div class="text-sm text-gray-600">Selected: {{ selected.join(', ') || 'none' }}</div>
            </div>
        `,
    }),
};

const disabledCode = `
<ToggleGroup v-model="selected">
    <ToggleItem value="option1" label="Option 1" />
    <ToggleItem value="option2" label="Option 2" disabled />
    <ToggleItem value="option3" label="Option 3" />
</ToggleGroup>
`;

export const Disabled: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: disabledCode },
        },
    },
    render: () => ({
        components: { ToggleGroup, ToggleItem },
        setup() {
            const selected = ref('option1');
            return { selected };
        },
        template: disabledCode,
    }),
};
