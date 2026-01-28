import type {Meta, StoryObj} from '@storybook/vue3';
import {Button, Description, Field, Input, Label} from '@ui';
import {icons} from "@/stories/icons";

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

const defaultCode = `
<Input name="email" type="email" placeholder="Email goes here" />
`;

export const _DocsIntro: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: defaultCode }
        }
    },
    render: () => ({
        components: { Input },
        template: defaultCode,
    }),
};

const labelsCode = `
<Field>
    <Label required>Email</Label>
    <Description>We need it so we can sell your info to spammers.</Description>
    <Input name="email" type="email" />
</Field>
`;

export const _LabelsDescriptions: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: labelsCode }
        }
    },
    render: () => ({
        components: { Input, Field, Label, Description },
        template: labelsCode,
    }),
};

const typesCode = `
<div class="space-y-3">
    <Input type="email" label="Email" placeholder="Email" />
    <Input type="password" label="Password" placeholder="Password" />
    <Input type="date" label="Date" placeholder="Date" />
</div>
`;

export const _Types: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: typesCode }
        }
    },
    render: () => ({
        components: { Input },
        template: typesCode,
    }),
};

const iconsCode = `
<div class="space-y-3">
    <Input label="Email" icon="mail" placeholder="jim@bob.com" />
    <Input label="Email" icon-append="mail" placeholder="jim@bob.com" />
    <Input label="Email" icon-prepend="mail" placeholder="jim@bob.com" />
</div>
`;

export const _Icons: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: iconsCode }
        }
    },
    render: () => ({
        components: { Input },
        template: iconsCode,
    }),
};

const sizesCode = `
<div class="space-y-3">
    <Input type="text" placeholder="Default" icon-append="mail" />
    <Input size="sm" type="text" placeholder="Small" icon-append="mail" />
    <Input size="xs" type="text" placeholder="Extra Small" icon-append="mail" />
</div>
`;

export const _Sizes: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: sizesCode }
        }
    },
    render: () => ({
        components: { Input },
        template: sizesCode,
    }),
};

const slotsCode = `
<Input label="Email" placeholder="jim@bob.com">
    <template #append>
        <Button icon="arrow-right" variant="ghost" size="sm"/>
    </template>
</Input>
`;

export const _Slots: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: slotsCode }
        }
    },
    render: () => ({
        components: { Input, Button },
        template: slotsCode,
    }),
};

const clearableCode = `
<Input label="Email" clearable value="jim@bob.com" />
`;

export const _Clearable: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: clearableCode }
        }
    },
    render: () => ({
        components: { Input },
        template: clearableCode,
    }),
};

const copyableCode = `
<Input label="Secret" copyable readonly model-value="values.secret" />
`;

export const _Copyable: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: copyableCode }
        }
    },
    render: () => ({
        components: { Input },
        template: copyableCode,
    }),
};

const viewableCode = `
<Input label="Password" type="password" viewable model-value="values.password" />
`;

export const _Viewable: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: viewableCode }
        }
    },
    render: () => ({
        components: { Input },
        template: viewableCode,
    }),
};

const textPrependAppendCode = `
<Input name="url" prepend="https://" append=".com" value="statamic" />
`;

export const _TextPrependAppend: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: textPrependAppendCode }
        }
    },
    render: () => ({
        components: { Input },
        template: textPrependAppendCode,
    }),
};

const characterLimitCode = `
<Input
    name="toot"
    :limit="240"
    model-value="When you get close to the limit, you'll know."
/>
`;

export const _CharacterLimit: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: characterLimitCode }
        }
    },
    render: () => ({
        components: { Input },
        template: characterLimitCode,
    }),
};
