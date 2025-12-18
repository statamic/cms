import type {Meta, StoryObj} from '@storybook/vue3';
import {Avatar} from '@ui';

const meta = {
    title: 'Components/Avatar',
    component: Avatar,
    argTypes: {},
} satisfies Meta<typeof Avatar>;

export default meta;
type Story = StoryObj<typeof meta>;

const introCode = `
<Avatar :user="{ name: 'John Doe', avatar: 'https://i.pravatar.cc/150?img=1' }" />
<Avatar :user="{ name: 'Jane Smith' }" />
<Avatar :user="{ name: 'Bob Johnson' }" />
<Avatar :user="{ name: 'David Michael Hasselhoff', initials: 'DMH' }" />
`;

export const _DocsIntro: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: introCode }
        }
    },
    render: () => ({
        components: { Avatar },
        template: `
            <div class="flex gap-2">
                ${introCode}
            </div>
        `,
    }),
};