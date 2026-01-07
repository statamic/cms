import type {Meta, StoryObj} from '@storybook/vue3';
import {Button, Stack, StackClose, StackHeader, StackContent, StackFooter, Modal, ModalClose} from '@ui';

const meta = {
    title: 'Overlays/Stack',
    component: Stack,
    subcomponents: {
        StackHeader,
        StackContent,
        StackFooter,
        StackClose,
    },
    argTypes: {
        'opened': {
            description: 'Event handler called when the stack is opened.',
            table: {
                category: 'events',
                type: { summary: '() => void' },
            },
        },
        'update:open': {
            description: 'Event handler called when the open state of the stack changes.',
            table: {
                category: 'events',
                type: { summary: '(value: boolean) => void' },
            },
        },
    },
} satisfies Meta<typeof Stack>;

export default meta;
type Story = StoryObj<typeof meta>;

const defaultCode = `
<Stack>
    <template #trigger>
        <Button text="How neat is that?" />
    </template>

    That's pretty neat.
</Stack>
`;

export const _DocsIntro: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: defaultCode }
        }
    },
    render: () => ({
        components: { Stack, Button },
        template: defaultCode,
    }),
};

const closeButtonCode = `
<Stack>
    <StackClose>
        <Button text="Close" />
    </StackClose>
</Stack>
`;

export const _StackClose: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: {
                code: `
                    <Stack>
                        <StackClose>
                            <Button text="Close" />
                        </StackClose>
                    </Stack>
                `
            }
        }
    },
    render: () => ({
        components: { Stack, StackClose, Button },
        template: `
            <Stack>
                <template #trigger>
                    <Button text="Open" />
                </template>
                <StackClose>
                    <Button text="Close" />
                </StackClose>
            </Stack>

        `,
    }),
};

export const _CloseViaSlotProp: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: {
                code: `
                    <Stack v-slot="{ close }">
                        <SomeComponent @finished="close" />
                    </Stack>
                `
            }
        }
    },
    render: () => ({
        components: { Stack, Button },
        template: `
            <Stack>
                <template #trigger>
                    <Button text="Open" />
                </template>
                <template #default="{ close }">
                    <Button text="Close" @click="close" />
                </template>
            </Stack>
        `,
    }),
};

const iconCode = `
<Stack
    v-model:open="isOpen"
    title="That's Pretty Neat"
    icon="fire-flame-burn-hot"
>
    <template #trigger>
        <Button text="How neat is that?" />
    </template>
</Stack>
`;

export const _WithIcon: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: { code: iconCode }
        }
    },
    render: () => ({
        components: { Stack, Button },
        data: () => {
            return { isOpen: false };
        },
        template: iconCode,
    }),
};

const beforeCloseCode = `
<Stack
    v-model:open="isOpen"
    :before-close="confirmClose"
>
    <template #trigger>
        <Button text="Close" />
    </template>
    When you try to close me, you'll be asked to confirm.
</Stack>
`;

export const BeforeClose: Story = {
    parameters: {
        docs: {
            source: {
                code: `
<template>
    <Stack :before-close="() => confirm('Are you sure?')">
        When you try to close me, you'll be asked to confirm.
    </Stack>
</template>
            `
            }
        }
    },
    render: () => ({
        components: { Stack, Button },
        data: () => {
            return {
                confirmClose: () => confirm('Are you sure?'),
            };
        },
        template: `
            <Stack :before-close="confirmClose">
                <template #trigger>
                    <Button text="Open" />
                </template>
                When you try to close me, you'll be asked to confirm.
            </Stack>`,
    }),
};


const viaTriggerCode = `
    <Stack>
        <template #trigger>
            <Button text="Open" />
        </template>

        I'm a stack.
    </Stack>
`;
export const ViaTrigger: Story = {
    parameters: {
        docs: {
            source: {
                code: viaTriggerCode
            }
        }
    },
    render: () => ({
        components: { Stack, Button },
        template: viaTriggerCode
    }),
};

const viaPropTemplate = `
    <Button text="Open" @click="isOpen = true" />

    <Stack v-model:open="isOpen">
        I'm a stack.
    </Stack>
`;
const viaPropCode = `
<script setup>
import { ref } from 'vue';
const isOpen = ref(false);
</script>

<template>${viaPropTemplate}</template>
`;
export const ViaProp: Story = {
    parameters: {
        docs: {
            source: {
                code: viaPropCode
            }
        }
    },
    render: () => ({
        components: { Stack, Button },
        data: () => ({ isOpen: false }),
        template: viaPropTemplate
    }),
};

export const Nesting: Story = {
    parameters: {
        docs: {
            source: {
                code: `
<Stack title="First">
    <First />
</Stack>

// First.vue
<template>
    <Stack title="Second">
        <Second />
    </Stack>
</template>

// Second.vue
<template>
    <Stack title="Third">
        and so on...
    </Stack>
</template>
                `
            }
        }
    },
    render: () => ({
        components: { Stack, StackClose, Modal, ModalClose, Button },
        template: `
            <Stack title="First">
                <template #trigger>
                    <Button text="Open" />
                </template>

                <Stack title="Second">
                    <template #trigger>
                        <Button text="Open Second Stack" />
                    </template>

                    <Modal>
                        <template #trigger>
                            <Button text="Open Modal" />
                        </template>

                        <Stack title="Third">
                            <template #trigger>
                                <Button text="Open Third Stack" />
                            </template>

                            <Stack title="Fourth">
                                <template #trigger>
                                    <Button text="Open Fourth Stack" />
                                </template>

                                <StackClose>
                                    <Button text="Okay that's enough" />
                                </StackClose>
                            </Stack>
                        </Stack>
                    </Modal>

                </Stack>
            </Stack>
        `
    }),
};

export const AutomaticHeader: Story = {
    parameters: {
        docs: {
            source: {
                code: `
                    <Stack title="Stack Title" icon="cog">
                        Lots of content...
                    </Stack>
                `
            }
        }
    },
    render: () => ({
        components: { Stack, Button },
        template: `
            <Stack title="Stack Title" icon="cog">
                <template #trigger>
                    <Button text="Open" />
                </template>
                <div v-for="n in 200" :key="n">Lots of content...</div>
            </Stack>
        `
    }),
};

export const Composed: Story = {
    parameters: {
        docs: {
            source: {
                code: `
<Stack>
    <StackHeader title="Composed Header" icon="cog" />
    <StackContent>Lots of content...</StackContent>
    <StackFooter>I'm the footer.</StackFooter>
</Stack>
                `
            }
        }
    },
    render: () => ({
        components: { Stack, StackHeader, StackContent, StackFooter, Button },
        template: `
            <Stack>
                <template #trigger>
                    <Button text="Open" />
                </template>
                <StackHeader title="Composed Header" icon="cog" />
                <StackContent>
                    <div v-for="n in 200" :key="n">Lots of content...</div>
                </StackContent>
                <StackFooter>I'm the footer.</StackFooter>
            </Stack>
        `
    }),
};

export const Sizes: Story = {
    parameters: {
        docs: {
            source: {
                code: `
                    <Stack> Default! </Stack>

                    <Stack size="narrow"> Narrow! </Stack>

                    <Stack size="full"> Full width! </Stack>
                `
            }
        }
    },
    render: () => ({
        components: { Stack, StackHeader, StackContent, StackFooter, Button },
        template: `
            <div class="flex gap-2">
                <Stack>
                    <template #trigger>
                        <Button text="Default" />
                    </template>
                    Default!
                </Stack>
                <Stack size="narrow">
                    <template #trigger>
                        <Button text="Narrow" />
                    </template>
                    Narrow!
                </Stack>
                <Stack size="full">
                    <template #trigger>
                        <Button text="Full Width" />
                    </template>
                    Full width!
                </Stack>
            </div>
        `
    }),
};
