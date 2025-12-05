import type { Meta, StoryObj } from '@storybook/vue3';
import { Icon, Input, CardPanel } from '@ui';
import { ref, computed } from 'vue';

const meta = {
    title: 'Components/Icon',
    component: Icon,
    argTypes: {
        name: { control: 'text' },
        set: { control: 'text' },
    },
} satisfies Meta<typeof Icon>;

export default meta;
type Story = StoryObj<typeof meta>;

export const Default: Story = {
    args: {
        name: 'plus',
    },
};

export const _DocsIntro: Story = {
    tags: ['!dev'],
    args: {
        name: 'ai-spark',
    },
};

const iconFiles = import.meta.glob('../../svg/icons/*.svg');
const icons = Object.keys(iconFiles).map((path) => {
    const parts = path.split('/');
    const fileName = parts[parts.length - 1];
    return fileName.replace('.svg', '');
}).sort();

export const AllIcons: Story = {
    argTypes: {
        name: { control: { disable: true } },
    },
    parameters: {
        docs: {
            source: {
                code: `
                    <Icon name="plus" />
                    <Icon name="star" />
                    <Icon name="heart" />
                    // ... and more
                `,
            },
        },
    },
    render: () => ({
        components: { Icon, Input, CardPanel },
        setup() {
            const search = ref('');
            const filteredIcons = computed(() => {
                if (!search.value) return icons;
                return icons.filter(name => name.toLowerCase().includes(search.value.toLowerCase()));
            });
            const copyToClipboard = (string: string) => {
                navigator.clipboard.writeText(string);
            }
            return { search, filteredIcons, copyToClipboard };
        },
        template: `
            <div class="space-y-4">
                <div class="">
                    <Input
                        v-model="search"
                        type="text"
                        placeholder="Search icons..."
                    />
                </div>

                <div class="grid grid-cols-4 md:grid-cols-6 2xl:grid-cols-10 gap-4">
                    <div
                        v-for="icon in filteredIcons"
                        class="group relative bg-gray-50 rounded-lg py-6 px-2 flex flex-col items-center gap-2 overflow-hidden"
                    >
                        <div class="absolute inset-1 grid grid-cols-1 gap-1 text-xs text-center invisible group-hover:visible">
                            <div
                                class="flex items-center justify-center bg-gray-300 rounded cursor-pointer"
                                @click="copyToClipboard(icon)"
                            >
                                <span>Copy Name</span>
                            </div>
                            <div
                                class="flex items-center justify-center bg-gray-300 rounded cursor-pointer"
                                @click="copyToClipboard('<Icon name=&quot;' + icon + '&quot; />')"
                            >
                                <span>Copy Icon</span>
                            </div>
                        </div>
                        <Icon :name="icon" class="size-6" />
                        <div class="text-xs text-gray-500 truncate">{{ icon }}</div>
                    </div>
                </div>
            </div>
        `,
    }),
};
