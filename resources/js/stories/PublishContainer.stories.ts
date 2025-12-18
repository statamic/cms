import type { Meta, StoryObj } from '@storybook/vue3';
import { PublishContainer } from '@ui';

const meta = {
    title: 'Components/PublishContainer',
    component: PublishContainer,
    parameters: {
        docs: {
            description: {
                component: 'The PublishContainer component is used to create publish forms in the Control Panel. For full documentation including backend setup, visit the [Publish Forms documentation](https://v6.statamic.dev/control-panel/publish-forms).',
            },
        },
    },
} satisfies Meta<typeof PublishContainer>;

export default meta;
type Story = StoryObj<typeof meta>;

export const _DocsIntro: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: {
                code: 'For complete documentation and examples, please visit:\nhttps://v6.statamic.dev/control-panel/publish-forms',
            },
        },
    },
    render: () => ({
        template: `
            <div class="rounded-xl border border-gray-200 dark:border-gray-800 p-6 text-center">
                <h3 class="text-lg font-semibold mb-2">Publish Container Documentation</h3>
                <p class="text-gray-600 dark:text-gray-400 mb-4">
                    The PublishContainer component requires backend configuration and blueprint setup.
                </p>
                <a 
                    href="https://v6.statamic.dev/control-panel/publish-forms" 
                    target="_blank"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600"
                >
                    View Full Documentation
                    <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                    </svg>
                </a>
            </div>
        `,
    }),
};
