import type { Meta, StoryObj } from '@storybook/vue3';
import { Card, CardPanel, Panel, PanelHeader, Heading, Subheading, Input, Button } from '@ui';

const meta = {
    title: 'Components/Card',
    component: Card,
    argTypes: {
        inset: { control: 'boolean' },
        variant: {
            control: 'select',
            options: ['default', 'flat'],
        },
    },
} satisfies Meta<typeof Card>;

export default meta;
type Story = StoryObj<typeof meta>;

const defaultCode = `
<Card class="space-y-6 w-92 mx-auto">
    <header>
        <Heading size="lg">Create a new account</Heading>
        <Subheading>Welcome to the thing! You're gonna love it here.</Subheading>
    </header>
    <Input label="Name" placeholder="Your name" />
    <Input label="Email" type="email" placeholder="Your email" />
    <div class="space-y-2 pt-6">
        <Button variant="primary" class="w-full" text="Continue" type="submit" />
        <Button variant="ghost" class="w-full">Already have an account? Go sign in</Button>
    </div>
</Card>
`;

export const _DocsIntro: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: defaultCode }
        }
    },
    render: () => ({
        components: { Card, Heading, Subheading, Input, Button },
        template: defaultCode,
    }),
};

const insetCode = `
<Card inset class="w-64">
    <img class="rounded-t-xl max-w-full" src="https://images.unsplash.com/photo-1549524362-47d913ec9a0e?q=80&w=640&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D" />
    <div class="p-4 text-sm">
        <strong>This is me</strong>. I am an amazing motocross stunt driver and am available for weddings, parties, and bar mitzvahs.
    </div>
</Card>
`;

export const _Inset: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: insetCode }
        }
    },
    render: () => ({
        components: { Card },
        template: insetCode,
    }),
};

const variantsCode = `
<div class="flex items-center justify-center gap-3">
    <Card class="size-30">Default</Card>
    <Card class="size-30" variant="flat">Flat</Card>
</div>
`;

export const _Variants: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: variantsCode }
        }
    },
    render: () => ({
        components: { Card },
        template: variantsCode,
    }),
};

const cardPanelCode = `
<CardPanel heading="Card Panel">
    This is a card panel.
</CardPanel>
`;

export const _CardPanel: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: cardPanelCode }
        }
    },
    render: () => ({
        components: { CardPanel },
        template: cardPanelCode,
    }),
};

const composedCode = `
<Panel>
    <PanelHeader class="flex items-center justify-between">
        <Heading text="Composed Card Panel" />
        <Button icon="download" text="Action" size="sm" />
    </PanelHeader>
    <Card>
        This is a composed card panel.
    </Card>
</Panel>
`;

export const _Composed: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: composedCode }
        }
    },
    render: () => ({
        components: { Panel, PanelHeader, Card, Heading, Button },
        template: composedCode,
    }),
};
