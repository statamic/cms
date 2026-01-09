import type {Meta, StoryObj} from '@storybook/vue3';
import { Button, Badge, ConfirmationModal, Icon } from '@ui';

const meta = {
    title: 'Overlays/ConfirmationModal',
    component: ConfirmationModal,
} satisfies Meta<typeof ConfirmationModal>;

export default meta;
type Story = StoryObj<typeof meta>;

export const _DocsIntro: Story = {
    tags: ['!dev'],
    parameters: {
        docs: {
            source: {
                code: `
                    <ConfirmationModal
                        v-model:open="isConfirming"
                        body-text="Do you really want to do that?"
                        @confirm="hasConfirmed = true"
                    />
                `
            }
        }
    },
    render: () => ({
        components: { ConfirmationModal, Button, Badge },
        data: () => ({
            isConfirming: false,
            hasConfirmed: false,
        }),
        template: `
            <div class="flex items-center gap-2">
                <Button text="Confirm" @click="isConfirming = true" />
                <Badge color="green" text="Confirmed" icon="checkmark" v-if="hasConfirmed" />
                <ConfirmationModal
                    v-model:open="isConfirming"
                    body-text="Do you really want to do that?"
                    @confirm="hasConfirmed = true"
                />
            </div>
        `,
    }),
};


export const ViaProp: Story = {
    render: () => ({
        components: { ConfirmationModal, Button, Icon },
        data: () => ({
            isOpen: false,
            isConfirmed: false,
        }),
        template: `
            <div class="flex items-center gap-2">
                <Button
                    :text="isConfirmed ? 'Confirmed' : 'Confirm'"
                    :disabled="isConfirmed"
                    :icon="isConfirmed ? 'checkmark' : null"
                    @click="isOpen = true"
                />

                <Button
                    v-if="isConfirmed"
                    icon="x"
                    text="Reset"
                    variant="ghost"
                    @click="isConfirmed = false"
                />
            </div>

            <ConfirmationModal
                v-model:open="isOpen"
                @confirm="isConfirmed = true"
            />
        `
    }),
};



export const Busy: Story = {
    render: () => ({
        components: { ConfirmationModal, Button, Icon },
        data: () => ({
            isOpen: false,
            isConfirmed: false,
            isBusy: false,
        }),
        methods: {
            confirm() {
                this.isBusy = true;
                setTimeout(() => {
                    this.isConfirmed = true;
                    this.isBusy = false;
                    this.isOpen = false;
                }, 1000);
            }
        },
        template: `
            <div class="flex items-center gap-2">
                <Button
                    :text="isConfirmed ? 'Confirmed' : 'Confirm'"
                    :disabled="isConfirmed"
                    :icon="isConfirmed ? 'checkmark' : null"
                    :loading="isBusy"
                    @click="isOpen = true"
                />

                <Button
                    v-if="isConfirmed"
                    icon="x"
                    text="Reset"
                    variant="ghost"
                    @click="isConfirmed = false"
                />
            </div>

            <ConfirmationModal
                v-model:open="isOpen"
                :busy="isBusy"
                @confirm="confirm"
            />
        `
    }),
};
