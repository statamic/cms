import type {Meta, StoryObj} from '@storybook/vue3';
import {ref} from 'vue';
import {Button, Field, Input, Switch, Textarea} from '@statamic/cms/ui';

const meta = {
    title: 'Forms/Field',
    component: Field,
    argTypes: {
        variant: {
            control: 'select',
            options: ['block', 'inline'],
        },
    },
} satisfies Meta<typeof Field>;

export default meta;
type Story = StoryObj<typeof meta>;

export const _DocsIntro: Story = {
    tags: ['!dev'],
    render: () => ({
        components: { Field, Input },
        template: `
            <Field label="Email" instructions="Your primary email address">
                <Input placeholder="jim@bob.com" />
            </Field>
        `,
    }),
};

export const _Required: Story = {
    tags: ['!dev'],
    render: () => ({
        components: { Field, Input },
        template: `
            <Field label="Email" required instructions="We need this to contact you">
                <Input placeholder="jim@bob.com" />
            </Field>
        `,
    }),
};

export const _WithBadge: Story = {
    tags: ['!dev'],
    render: () => ({
        components: { Field, Input },
        template: `
            <Field label="Theme Color" badge="New" instructions="Customize your site's color scheme">
                <Input placeholder="#3490dc" />
            </Field>
        `,
    }),
};

export const _WithError: Story = {
    tags: ['!dev'],
    render: () => ({
        components: { Field, Input },
        template: `
            <Field label="Email" error="This email is already taken" required>
                <Input placeholder="jim@bob.com" />
            </Field>
        `,
    }),
};

export const _InlineVariant: Story = {
    tags: ['!dev'],
    render: () => ({
        components: { Field, Switch },
        setup() {
            const enabled = ref(false);
            return { enabled };
        },
        template: `
            <Field label="Enable notifications" variant="inline">
                <Switch v-model="enabled" />
            </Field>
        `,
    }),
};

export const _InstructionsBelow: Story = {
    tags: ['!dev'],
    render: () => ({
        components: { Field, Textarea },
        template: `
            <Field
                label="Bio"
                instructions="Tell us a bit about yourself. Markdown is supported."
                instructions-below
            >
                <Textarea placeholder="I love building websites..." />
            </Field>
        `,
    }),
};

export const _WithActionsSlot: Story = {
    tags: ['!dev'],
    render: () => ({
        components: { Field, Input, Button },
        template: `
            <Field label="Title">
                <template #actions>
                    <Button text="Clear" size="xs" variant="ghost" />
                </template>
                <Input placeholder="Enter a title..." />
            </Field>
        `,
    }),
};

export const _Inline: Story = {
    tags: ['!dev'],
    render: () => ({
        components: { Field, Input, Textarea },
        template: `
            <div class="bg-white dark:bg-gray-900 rounded-lg divide-y divide-gray-200 dark:divide-gray-800">
                <Field label="Site Name" inline>
                    <Input value="My Awesome Site" />
                </Field>
                <Field label="Tagline" inline>
                    <Input value="Just another Statamic site" />
                </Field>
                <Field label="Description" inline full-width-setting>
                    <Textarea value="A brief description of my site" />
                </Field>
            </div>
        `,
    }),
};
