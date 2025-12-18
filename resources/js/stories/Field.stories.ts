import type {Meta, StoryObj} from '@storybook/vue3';
import {ref} from 'vue';
import {Button, Field, Input, Switch, Textarea} from '@ui';

const meta = {
    title: 'Forms/Field',
    component: Field,
    argTypes: {
        asConfig: {
            control: 'boolean',
            description: 'When `true`, styles the field as a configuration field with a two-column grid layout.',
        },
        badge: {
            control: 'text',
            description: 'Badge text to display next to the label.',
        },
        disabled: { control: 'boolean' },
        error: {
            control: 'text',
            description: 'Error message to display below the field.',
        },
        errors: {
            control: 'object',
            description: 'Object or array of error messages to display below the field.',
        },
        fullWidthSetting: {
            control: 'boolean',
            description: 'When `true`, forces the field to use full width even when `asConfig` is enabled.',
        },
        id: { control: 'text' },
        instructions: {
            control: 'text',
            description: 'Instructions text to display above or below the label. Supports Markdown.',
        },
        instructionsBelow: {
            control: 'boolean',
            description: 'When `true`, displays instructions below the control instead of below the label.',
        },
        label: {
            control: 'text',
            description: 'Label text for the field.',
        },
        readOnly: { control: 'boolean' },
        required: { control: 'boolean' },
        variant: {
            control: 'select',
            description: 'Controls the layout of the field. <br><br> Options: `block`, `inline`',
            options: ['block', 'inline'],
        },
    },
} satisfies Meta<typeof Field>;

export default meta;
type Story = StoryObj<typeof meta>;

const defaultCode = `
<Field label="Email" instructions="Your primary email address">
    <Input placeholder="jim@bob.com" />
</Field>
`;

export const _DocsIntro: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: defaultCode }
        }
    },
    render: () => ({
        components: { Field, Input },
        template: defaultCode,
    }),
};

const requiredCode = `
<Field label="Email" required instructions="We need this to contact you">
    <Input placeholder="jim@bob.com" />
</Field>
`;

export const _Required: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: requiredCode }
        }
    },
    render: () => ({
        components: { Field, Input },
        template: requiredCode,
    }),
};

const withBadgeCode = `
<Field label="Theme Color" badge="New" instructions="Customize your site's color scheme">
    <Input placeholder="#3490dc" />
</Field>
`;

export const _WithBadge: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: withBadgeCode }
        }
    },
    render: () => ({
        components: { Field, Input },
        template: withBadgeCode,
    }),
};

const withErrorCode = `
<Field label="Email" error="This email is already taken" required>
    <Input placeholder="jim@bob.com" />
</Field>
`;

export const _WithError: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: withErrorCode }
        }
    },
    render: () => ({
        components: { Field, Input },
        template: withErrorCode,
    }),
};

const inlineVariantCode = `
<Field label="Enable notifications" variant="inline">
    <Switch v-model="enabled" />
</Field>
`;

export const _InlineVariant: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: inlineVariantCode }
        }
    },
    render: () => ({
        components: { Field, Switch },
        setup() {
            const enabled = ref(false);
            return { enabled };
        },
        template: inlineVariantCode,
    }),
};

const instructionsBelowCode = `
<Field 
    label="Bio" 
    instructions="Tell us a bit about yourself. Markdown is supported."
    instructions-below
>
    <Textarea placeholder="I love building websites..." />
</Field>
`;

export const _InstructionsBelow: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: instructionsBelowCode }
        }
    },
    render: () => ({
        components: { Field, Textarea },
        template: instructionsBelowCode,
    }),
};

const withActionsSlotCode = `
<Field label="Title">
    <template #actions>
        <Button text="Clear" size="xs" variant="ghost" />
    </template>
    <Input placeholder="Enter a title..." />
</Field>
`;

export const _WithActionsSlot: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: withActionsSlotCode }
        }
    },
    render: () => ({
        components: { Field, Input, Button },
        template: withActionsSlotCode,
    }),
};

const asConfigCode = `
<div class="bg-white dark:bg-gray-900 rounded-lg divide-y divide-gray-200 dark:divide-gray-800">
    <Field label="Site Name" as-config>
        <Input value="My Awesome Site" />
    </Field>
    <Field label="Tagline" as-config>
        <Input value="Just another Statamic site" />
    </Field>
    <Field label="Description" as-config full-width-setting>
        <Textarea value="A brief description of my site" />
    </Field>
</div>
`;

export const _AsConfig: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: asConfigCode }
        }
    },
    render: () => ({
        components: { Field, Input, Textarea },
        template: asConfigCode,
    }),
};
