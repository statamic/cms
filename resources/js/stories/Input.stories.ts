import type {Meta, StoryObj} from '@storybook/vue3';
import {Button, Description, Field, Input, Label} from '@ui';
import {icons} from "@/stories/icons";

/**
 * @import import { Input } from '@statamic/cms/ui';
 */
const meta = {
    title: 'Forms/Input',
    component: Input,
    argTypes: {
        icon: {
            control: 'select',
            options: icons,
        },
        iconAppend: {
            control: 'select',
            options: icons,
        },
        iconPrepend: {
            control: 'select',
            options: icons,
        },
        size: {
            control: 'select',
            options: ['xs', 'sm', 'base'],
        },
        variant: {
            control: 'select',
            options: ['default', 'light', 'ghost'],
        },
        'update:modelValue': {
            description: 'Event handler called when the input is updated.',
            table: {
                category: 'events',
                type: { summary: '(value: string) => void' }
            }
        }
    },
} satisfies Meta<typeof Input>;

export default meta;
type Story = StoryObj<typeof meta>;

export const _DocsIntro: Story = {
    tags: ['!dev'],
    render: () => ({
        components: { Input },
        template: `
            <Input name="email" type="email" placeholder="Email goes here" />
        `,
    }),
};

export const _LabelsDescriptions: Story = {
    tags: ['!dev'],
    render: () => ({
        components: { Input, Field, Label, Description },
        template: `
            <Field>
                <Label required>Email</Label>
                <Description>We need it so we can sell your info to spammers.</Description>
                <Input name="email" type="email" />
            </Field>
        `,
    }),
};

export const _Types: Story = {
    tags: ['!dev'],
    render: () => ({
        components: { Input },
        template: `
            <div class="space-y-3">
                <Input type="email" label="Email" placeholder="Email" />
                <Input type="password" label="Password" placeholder="Password" />
                <Input type="date" label="Date" placeholder="Date" />
            </div>
        `,
    }),
};

export const _Icons: Story = {
    tags: ['!dev'],
    render: () => ({
        components: { Input },
        template: `
            <div class="space-y-3">
                <Input label="Email" icon="mail" placeholder="jim@bob.com" />
                <Input label="Email" icon-append="mail" placeholder="jim@bob.com" />
                <Input label="Email" icon-prepend="mail" placeholder="jim@bob.com" />
            </div>
        `,
    }),
};

export const _Sizes: Story = {
    tags: ['!dev'],
    render: () => ({
        components: { Input },
        template: `
            <div class="space-y-3">
                <Input type="text" placeholder="Default" icon-append="mail" />
                <Input size="sm" type="text" placeholder="Small" icon-append="mail" />
                <Input size="xs" type="text" placeholder="Extra Small" icon-append="mail" />
            </div>
        `,
    }),
};

export const _Slots: Story = {
    tags: ['!dev'],
    render: () => ({
        components: { Input, Button },
        template: `
            <Input label="Email" placeholder="jim@bob.com">
                <template #append>
                    <Button icon="arrow-right" variant="ghost" size="sm"/>
                </template>
            </Input>
        `,
    }),
};

export const _Clearable: Story = {
    tags: ['!dev'],
    render: () => ({
        components: { Input },
        template: `
            <Input label="Email" clearable value="jim@bob.com" />
        `,
    }),
};

export const _Copyable: Story = {
    tags: ['!dev'],
    render: () => ({
        components: { Input },
        template: `
            <Input label="Secret" copyable readonly model-value="values.secret" />
        `,
    }),
};

export const _Viewable: Story = {
    tags: ['!dev'],
    render: () => ({
        components: { Input },
        template: `
            <Input label="Password" type="password" viewable model-value="values.password" />
        `,
    }),
};

export const _TextPrependAppend: Story = {
    tags: ['!dev'],
    render: () => ({
        components: { Input },
        template: `
            <Input name="url" prepend="https://" append=".com" value="statamic" />
        `,
    }),
};

export const _CharacterLimit: Story = {
    tags: ['!dev'],
    render: () => ({
        components: { Input },
        template: `
            <Input
                name="toot"
                :limit="240"
                model-value="When you get close to the limit, you'll know."
            />
        `,
    }),
};
