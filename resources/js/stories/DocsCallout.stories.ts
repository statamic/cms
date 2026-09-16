import type {Meta, StoryObj} from '@storybook/vue3';
import {DocsCallout} from '@ui';

/**
 * @import import { DocsCallout } from '@statamic/cms/ui';
 */
const meta = {
    title: 'Components/DocsCallout',
    component: DocsCallout,
    argTypes: {},
} satisfies Meta<typeof DocsCallout>;

export default meta;
type Story = StoryObj<typeof meta>;

export const _DocsIntro: Story = {
    tags: ['!dev'],
    render: () => ({
        components: { DocsCallout },
        template: `
            <DocsCallout topic="Blueprints" url="content-modeling/blueprints" />
        `,
    }),
};

export const _ThirdParty: Story = {
    tags: ['!dev'],
    render: () => ({
        components: { DocsCallout },
        template: `
            <DocsCallout topic="SEO Pro" url="https://github.com/statamic/seo-pro" />
        `,
    }),
};
