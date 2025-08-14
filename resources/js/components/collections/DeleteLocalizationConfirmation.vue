<template>
    <Modal :title="__('Delete')" :open="true" @update:open="$emit('cancel')">
        <p>Are you sure you want to delete this?</p>

        <Field
            :errors="error ? [__('statamic::validation.required')] : null"
            :instructions
            :label="__('Localizations')"
        >
            <ButtonGroup ref="buttonGroup">
                <Button
                    ref="button"
                    :name="name"
                    @click="behavior = 'delete'"
                    value="delete"
                    :variant="behavior === 'delete' ? 'primary' : 'default'"
                    :text="__('Delete')"
                />

                <Button
                    ref="button"
                    :name="name"
                    @click="behavior = 'copy'"
                    value="copy"
                    :variant="behavior === 'copy' ? 'primary' : 'default'"
                    :text="__('Detach')"
                />
            </ButtonGroup>
        </Field>

        <template #footer>
            <div class="flex items-center justify-end space-x-3 pt-3 pb-1">
                <Button
                    variant="ghost"
                    @click="$emit('cancel')"
                    :text="__('Cancel')"
                />
                <Button variant="primary" @click="confirm" :text="__('Confirm')" />
            </div>
        </template>
    </Modal>
</template>

<script>
import { Modal, Field, Button, ButtonGroup } from '@statamic/cms/ui';

export default {
    components: {
        Modal,
        Field,
        Button,
        ButtonGroup,
    },

    props: {
        entries: { type: Number, required: true },
    },

    data() {
        return {
            behavior: null,
            error: false,
        };
    },

    computed: {
        instructions() {
            let url = docs_url('/tips/localizing-entries#deleting');

            return `${__('messages.choose_entry_localization_deletion_behavior')} <a href="${url}" target="_blank">${__('Learn more')}</a>`;
        },
    },

    methods: {
        confirm() {
            if (!this.behavior) {
                this.error = true;
                return;
            }

            this.$emit('confirm', this.behavior);
        },
    },
};
</script>
