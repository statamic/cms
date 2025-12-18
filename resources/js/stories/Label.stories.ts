import type {Meta, StoryObj} from '@storybook/vue3';
import {Field, Input, Label} from '@ui';

const meta = {
    title: 'Components/Label',
    component: Label,
    argTypes: {},
} satisfies Meta<typeof Label>;

export default meta;
type Story = StoryObj<typeof meta>;

const defaultCode = `
<Label text="Email Address" />
`;

export const _DocsIntro: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: defaultCode }
        }
    },
    render: () => ({
        components: { Label },
        template: defaultCode,
    }),
};

const requiredCode = `
<Label text="Email Address" required />
`;

export const _Required: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: requiredCode }
        }
    },
    render: () => ({
        components: { Label },
        template: requiredCode,
    }),
};

const withBadgeCode = `
<div class="space-y-3">
    <Label text="API Key" badge="Pro" />
    <Label text="Theme Color" badge="New" />
    <Label text="Advanced Settings" badge="Beta" />
</div>
`;

export const _WithBadge: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: withBadgeCode }
        }
    },
    render: () => ({
        components: { Label },
        template: withBadgeCode,
    }),
};

const withInputCode = `
<Field>
    <Label for="email" text="Email Address" required />
    <Input id="email" type="email" placeholder="you@example.com" />
</Field>
`;

export const _WithInput: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: withInputCode }
        }
    },
    render: () => ({
        components: { Label, Input, Field },
        template: withInputCode,
    }),
};

const withSlotCode = `
<Label>
    <strong>Email Address</strong>
    <span class="text-gray-500 ml-1 text-sm">(optional)</span>
</Label>
`;

export const _WithSlot: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: withSlotCode }
        }
    },
    render: () => ({
        components: { Label },
        template: withSlotCode,
    }),
};
