import type {Meta, StoryObj} from '@storybook/vue3';
import {DocsCallout} from '@ui';

const meta = {
    title: 'Components/DocsCallout',
    component: DocsCallout,
    argTypes: {},
} satisfies Meta<typeof DocsCallout>;

export default meta;
type Story = StoryObj<typeof meta>;

const defaultCode = `
<DocsCallout topic="Blueprints" url="content-modeling/blueprints" />
`;

export const _DocsIntro: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: defaultCode }
        }
    },
    render: () => ({
        components: { DocsCallout },
        template: defaultCode,
    }),
};

const thirdPartyCode = `
<DocsCallout topic="SEO Pro" url="https://github.com/statamic/seo-pro" />
`;

export const _ThirdParty: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: thirdPartyCode }
        }
    },
    render: () => ({
        components: { DocsCallout },
        template: thirdPartyCode,
    }),
};
