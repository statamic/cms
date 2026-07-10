import type {Meta, StoryObj} from '@storybook/vue3';
import {CardPanel, Icon, Input, registerIconSetFromStrings} from '@ui';
import {computed, ref} from 'vue';
import {icons} from "@/stories/icons";

// Inline all SVGs into the Storybook bundle so the AllIcons grid doesn't fire
// hundreds of requests (which Cloudflare flags as abuse). The CP bundle keeps
// the lazy glob in registry.js.
const eagerIcons = import.meta.glob('../../svg/icons/*.svg', {
    eager: true,
    query: '?raw',
    import: 'default',
}) as Record<string, string>;

registerIconSetFromStrings(
    'default',
    Object.fromEntries(
        Object.entries(eagerIcons).map(([path, svg]) => [
            path.split('/').pop()!.replace('.svg', ''),
            svg,
        ])
    )
);

registerIconSetFromStrings('storybook', {
    spark: `
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M12 2l1.9 6.1L20 10l-6.1 1.9L12 18l-1.9-6.1L4 10l6.1-1.9L12 2z" />
            <path d="M19 15l.8 2.2L22 18l-2.2.8L19 21l-.8-2.2L16 18l2.2-.8L19 15z" />
        </svg>
    `,
});

const meta = {
    title: 'Components/Icon',
    component: Icon,
    argTypes: {
        name: {
            control: 'select',
            options: icons,
        },
    },
} satisfies Meta<typeof Icon>;

export default meta;
type Story = StoryObj<typeof meta>;

export const Default: Story = {
    args: {
        name: 'plus',
    },
};

export const AlternateSetInName: Story = {
    args: {
        name: 'storybook::spark',
    },
    parameters: {
        docs: {
            source: {
                code: '<Icon name="storybook::spark" />',
            },
        },
    },
};

export const _DocsIntro: Story = {
    tags: ['!dev'],
    args: {
        name: 'ai-spark',
    },
};

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
