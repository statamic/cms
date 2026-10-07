import type {Meta, StoryObj} from '@storybook/vue3';
import {Button, Card, Description, Heading, Panel, PanelFooter, PanelHeader} from '@statamic/cms/ui';

const meta = {
    title: 'Layout/Panel',
    component: Panel,
    subcomponents: {
        PanelHeader,
        PanelFooter,
    },
    argTypes: {},
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

export const WithSubheading: Story = {
    tags: ['!dev'],
    render: () => ({
        components: { Panel, Card },
        template: `
            <Panel heading="Panel Title" subheading="This is a description of what this panel does">
                <Card>
                    Panel content goes here
                </Card>
            </Panel>
        `,
    }),
};

export const WithHeaderActions: Story = {
    tags: ['!dev'],
    render: () => ({
        components: { Panel, Card, Button },
        template: `
            <Panel heading="Panel Title">
                <template #header-actions>
                    <Button size="sm" variant="primary">Save</Button>
                </template>
                <Card>
                    Panel content goes here
                </Card>
            </Panel>
        `,
    }),
};

export const CustomHeader: Story = {
    tags: ['!dev'],
    render: () => ({
        components: { Panel, PanelHeader, Card, Heading, Description },
        template: `
            <Panel>
                <PanelHeader>
                    <Heading>Custom Header</Heading>
                    <Description>This example is using a custom header. You can <a href="#">link to things</a> in here, unlike a prop.</Description>
                </PanelHeader>
                <Card>
                    Panel content goes here
                </Card>
            </Panel>
        `,
    }),
};

export const WithFooter: Story = {
    tags: ['!dev'],
    render: () => ({
        components: { Panel, PanelFooter, Card, Button },
        template: `
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
        `,
    }),
};

export const NoHeader: Story = {
    tags: ['!dev'],
    render: () => ({
        components: { Panel, Card },
        template: `
            <Panel>
                <Card>
                    <div>
                        Panel content without a header
                    </div>
                </Card>
            </Panel>
        `,
    }),
};
