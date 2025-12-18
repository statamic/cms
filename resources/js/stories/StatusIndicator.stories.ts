import type {Meta, StoryObj} from '@storybook/vue3';
import {StatusIndicator} from '@ui';

const meta = {
    title: 'Components/StatusIndicator',
    component: StatusIndicator,
    argTypes: {
        status: {
            control: 'select',
            description: 'The status to display. <br><br> Options: `published`, `scheduled`, `expired`, `draft`, `hidden`',
            options: ['published', 'scheduled', 'expired', 'draft', 'hidden'],
        },
        showDot: {
            control: 'boolean',
            description: 'When `true`, displays a colored dot indicator.',
        },
        showLabel: {
            control: 'boolean',
            description: 'When `true`, displays the status label text.',
        },
        private: {
            control: 'boolean',
            description: 'When `true`, changes the appearance of published status to indicate private content.',
        },
    },
} satisfies Meta<typeof StatusIndicator>;

export default meta;
type Story = StoryObj<typeof meta>;

const defaultCode = `
<div class="flex items-center gap-4">
    <StatusIndicator status="published" />
    <StatusIndicator status="scheduled" />
    <StatusIndicator status="expired" />
    <StatusIndicator status="draft" />
</div>
`;

export const _DocsIntro: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: defaultCode }
        }
    },
    render: () => ({
        components: { StatusIndicator },
        template: defaultCode,
    }),
};

const withLabelCode = `
<div class="flex items-center gap-4">
    <StatusIndicator status="published" show-label />
    <StatusIndicator status="scheduled" show-label />
    <StatusIndicator status="expired" show-label />
    <StatusIndicator status="draft" show-label />
</div>
`;

export const _WithLabel: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: withLabelCode }
        }
    },
    render: () => ({
        components: { StatusIndicator },
        template: withLabelCode,
    }),
};

const privateCode = `
<div class="flex items-center gap-4">
    <StatusIndicator status="published" private />
    <StatusIndicator status="published" private show-label />
</div>
`;

export const _Private: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: privateCode }
        }
    },
    render: () => ({
        components: { StatusIndicator },
        template: privateCode,
    }),
};

const withoutDotCode = `
<div class="flex items-center gap-4">
    <StatusIndicator status="published" :show-dot="false" show-label />
    <StatusIndicator status="draft" :show-dot="false" show-label />
</div>
`;

export const _WithoutDot: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: withoutDotCode }
        }
    },
    render: () => ({
        components: { StatusIndicator },
        template: withoutDotCode,
    }),
};
