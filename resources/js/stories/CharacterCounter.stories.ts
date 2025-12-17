import type {Meta, StoryObj} from '@storybook/vue3';
import {CharacterCounter, Input} from '@ui';
import {ref} from 'vue';

const meta = {
    title: 'Components/CharacterCounter',
    component: CharacterCounter,
    argTypes: {
        text: {
            control: 'text',
            description: 'The text to count characters from.',
        },
        limit: {
            control: 'number',
            description: 'The maximum number of characters allowed.',
        },
        dangerZone: {
            control: 'number',
            description: 'Number of characters remaining before showing the countdown number (default: 20).',
        },
    },
} satisfies Meta<typeof CharacterCounter>;

export default meta;
type Story = StoryObj<typeof meta>;

const defaultCode = `
<CharacterCounter :text="text" :limit="100" />
`;

export const _DocsIntro: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: defaultCode }
        }
    },
    render: () => ({
        components: { CharacterCounter },
        setup() {
            const text = ref('This is some sample text.');
            return { text };
        },
        template: `<CharacterCounter :text="text" :limit="100" />`,
    }),
};

const withInputCode = `
<Input
    v-model="text"
    :limit="140"
    placeholder="What's happening?"
/>
`;

export const _WithInput: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: withInputCode }
        }
    },
    render: () => ({
        components: { Input },
        setup() {
            const text = ref('');
            return { text };
        },
        template: `<Input v-model="text" :limit="140" placeholder="What's happening?" />`,
    }),
};

const statesCode = `
<div class="flex gap-4 items-center">
    <div>
        <div class="text-xs text-gray-600 mb-2">Under 70%</div>
        <CharacterCounter :text="text1" :limit="100" />
    </div>
    <div>
        <div class="text-xs text-gray-600 mb-2">70-90%</div>
        <CharacterCounter :text="text2" :limit="100" />
    </div>
    <div>
        <div class="text-xs text-gray-600 mb-2">90-100%</div>
        <CharacterCounter :text="text3" :limit="100" />
    </div>
    <div>
        <div class="text-xs text-gray-600 mb-2">At limit</div>
        <CharacterCounter :text="text4" :limit="100" />
    </div>
    <div>
        <div class="text-xs text-gray-600 mb-2">Over limit</div>
        <CharacterCounter :text="text5" :limit="100" />
    </div>
</div>
`;

export const _States: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: statesCode }
        }
    },
    render: () => ({
        components: { CharacterCounter },
        setup() {
            const text1 = ref('Short text');
            const text2 = ref('This is a medium length text that is around seventy percent of the limit.');
            const text3 = ref('This is a longer text that is getting very close to the limit. We are now at about ninety perc');
            const text4 = ref('This is exactly one hundred characters long for testing the limit animation when you hit it nice');
            const text5 = ref('This text is way over the limit and should show a red circle with a strike through it to indicate the error state.');
            return { text1, text2, text3, text4, text5 };
        },
        template: `
            <div class="flex gap-4 items-center">
                <div>
                    <div class="text-xs text-gray-600 mb-2">Under 70%</div>
                    <CharacterCounter :text="text1" :limit="100" />
                </div>
                <div>
                    <div class="text-xs text-gray-600 mb-2">70-90%</div>
                    <CharacterCounter :text="text2" :limit="100" />
                </div>
                <div>
                    <div class="text-xs text-gray-600 mb-2">90-100%</div>
                    <CharacterCounter :text="text3" :limit="100" />
                </div>
                <div>
                    <div class="text-xs text-gray-600 mb-2">At limit</div>
                    <CharacterCounter :text="text4" :limit="100" />
                </div>
                <div>
                    <div class="text-xs text-gray-600 mb-2">Over limit</div>
                    <CharacterCounter :text="text5" :limit="100" />
                </div>
            </div>
        `,
    }),
};

const dangerZoneCode = `
<div class="flex gap-4 items-center">
    <div>
        <div class="text-xs text-gray-600 mb-2">Default (20)</div>
        <CharacterCounter :text="text" :limit="100" />
    </div>
    <div>
        <div class="text-xs text-gray-600 mb-2">Danger zone: 10</div>
        <CharacterCounter :text="text" :limit="100" :danger-zone="10" />
    </div>
    <div>
        <div class="text-xs text-gray-600 mb-2">Danger zone: 30</div>
        <CharacterCounter :text="text" :limit="100" :danger-zone="30" />
    </div>
</div>
`;

export const _DangerZone: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: dangerZoneCode }
        }
    },
    render: () => ({
        components: { CharacterCounter },
        setup() {
            const text = ref('This text is 85 characters long so we can see when the danger zone countdown appears!');
            return { text };
        },
        template: `
            <div class="flex gap-4 items-center">
                <div>
                    <div class="text-xs text-gray-600 mb-2">Default (20)</div>
                    <CharacterCounter :text="text" :limit="100" />
                </div>
                <div>
                    <div class="text-xs text-gray-600 mb-2">Danger zone: 10</div>
                    <CharacterCounter :text="text" :limit="100" :danger-zone="10" />
                </div>
                <div>
                    <div class="text-xs text-gray-600 mb-2">Danger zone: 30</div>
                    <CharacterCounter :text="text" :limit="100" :danger-zone="30" />
                </div>
            </div>
        `,
    }),
};
