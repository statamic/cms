import type {Meta, StoryObj} from '@storybook/vue3';
import {Button, Card, Description, Heading, Panel, PanelFooter, PanelHeader} from '@ui';

const meta = {
    title: 'Components/Panel',
    component: Panel,
    subcomponents: {
        PanelHeader,
        PanelFooter,
    },
    argTypes: {
        heading: {
            control: 'text',
            description: 'Heading text for the panel',
        },
        subheading: {
            control: 'text',
            description: 'Subheading text below the heading',
        },
    },
} satisfies Meta<typeof Panel>;

export default meta;
type Story = StoryObj<typeof meta>;

export const _DocsIntro: Story = {
    tags: ['!dev'],
    args: {
        heading: 'Panel Title',
        subheading: 'This is a description of what this panel does',
    },
    render: (args) => ({
        components: { Panel, Card },
        setup() {
            return { args };
        },
        template: `
            <Panel v-bind="args">
                <Card>
                    Panel content goes here
                </Card>
            </Panel>
        `,
    }),
};

export const Default: Story = {
    tags: ['!dev'],
    args: {
        heading: 'Panel Title',
    },
    render: (args) => ({
        components: { Panel, Card },
        setup() {
            return { args };
        },
        template: `
            <Panel v-bind="args">
                <Card>
                    Panel content goes here
                </Card>
            </Panel>
        `,
    }),
};

const withSubheadingCode = `
    <Panel heading="Panel Title" subheading="This is a description of what this panel does">
        <Card>
            Panel content goes here
        </Card>
    </Panel>
`;

export const WithSubheading: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: withSubheadingCode },
        },
    },
    render: () => ({
        components: { Panel, Card },
        template: withSubheadingCode,
    }),
};

const withHeaderActionsCode = `
    <Panel heading="Panel Title">
        <template #header-actions>
            <Button size="sm" variant="primary">Save</Button>
        </template>
        <Card>
            Panel content goes here
        </Card>
    </Panel>
`;

export const WithHeaderActions: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: withHeaderActionsCode },
        },
    },
    render: () => ({
        components: { Panel, Card, Button },
        template: withHeaderActionsCode,
    }),
};

const customHeaderCode = `
    <Panel>
        <PanelHeader>
            <Heading>Custom Header</Heading>
            <Description>This example is using a custom header. You can <a href="#">link to things</a> in here, unlike a prop.</Description>
        </PanelHeader>
        <Card>
            Panel content goes here
        </Card>
    </Panel>
`;

export const CustomHeader: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: customHeaderCode },
        },
    },
    render: () => ({
        components: { Panel, PanelHeader, Card, Heading, Description },
        template: customHeaderCode,
    }),
};

const footerCode = `
    <Panel heading="Panel Title">
        <Card>
            Panel content goes here
        </Card>
        <PanelFooter>
            <div class="flex justify-end gap-2">
                <Button text="Cancel" variant="ghost" />
                <Button text="Save" variant="primary" />
            </div>
        </PanelFooter>
    </Panel>
`;

export const WithFooter: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: footerCode },
        },
    },
    render: () => ({
        components: { Panel, PanelFooter, Card, Button },
        template: footerCode,
    }),
};

const noHeaderCode = `
    <Panel>
        <Card>
            <div>
                Panel content without a header
            </div>
        </Card>
    </Panel>
`;

export const NoHeader: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: noHeaderCode },
        },
    },
    render: () => ({
        components: { Panel, Card },
        template: noHeaderCode,
    }),
};
