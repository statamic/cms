<template>
    <Modal
        :open="show"
        :title="__('messages.asset_conflict_title')"
        @update:open="onOpenUpdated"
    >
        <p>{{ message }}</p>

        <template #footer>
            <div class="flex items-center justify-between gap-2 ps-2 pt-3 pb-1">
                <div>
                    <Checkbox
                        v-if="showApplyToAll"
                        :model-value="applyToAll"
                        :label="__('messages.asset_conflict_apply_to_all')"
                        @update:model-value="applyToAll = $event"
                    />
                </div>
                <div class="flex items-center gap-2">
                    <Button variant="ghost" :text="__('messages.asset_conflict_cancel')" @click="resolve('cancel')" />
                    <Button variant="default" :text="__('messages.asset_conflict_keep_both')" @click="resolve('timestamp')" />
                    <Button variant="danger" :text="__('messages.asset_conflict_overwrite')" @click="resolve('overwrite')" />
                </div>
            </div>
        </template>
    </Modal>
</template>

<script>
import { Button, Checkbox, Modal } from '@ui';

export default {
    components: {
        Button,
        Checkbox,
        Modal,
    },

    props: {
        uploads: {
            type: Array,
            default: () => [],
        },
        allowSelectingExisting: {
            type: Boolean,
            default: false,
        },
    },

    emits: ['existing-selected', 'resolved'],

    data() {
        return {
            show: false,
            applyToAll: false,
            policy: null,
            queue: [],
            currentId: null,
            message: '',
        };
    },

    computed: {
        conflictIds() {
            return this.uploads
                .filter((upload) => upload.errorStatus === 409)
                .map((upload) => upload.id);
        },

        conflictKey() {
            return this.conflictIds.join(',');
        },

        showApplyToAll() {
            return this.conflictIds.length > 1;
        },

        hasInFlightUploads() {
            return this.uploads.some((upload) => !upload.errorStatus && !upload.errorMessage);
        },
    },

    watch: {
        conflictKey() {
            this.syncConflicts();
        },
    },

    methods: {
        syncConflicts() {
            const ids = this.conflictIds;

            if (ids.length === 0) {
                this.queue = [];
                this.currentId = null;
                this.message = '';
                this.show = false;
                this.applyToAll = false;

                if (!this.hasInFlightUploads) {
                    this.policy = null;
                }

                return;
            }

            this.queue = this.queue.filter((id) => ids.includes(id));

            if (this.policy) {
                this.uploads
                    .filter((upload) => upload.errorStatus === 409)
                    .forEach((upload) => this.apply(upload, this.policy));

                return;
            }

            ids.forEach((id) => this.enqueue(id));
            this.openNext();
        },

        getUploadById(id) {
            return this.uploads.find((upload) => upload.id === id);
        },

        enqueue(id) {
            if (!id || this.queue.includes(id)) {
                return;
            }

            this.queue.push(id);
        },

        dequeue(id) {
            if (!id) {
                return;
            }

            this.queue = this.queue.filter((queuedId) => queuedId !== id);
        },

        open(upload) {
            this.currentId = upload.id;
            this.message = __('messages.asset_upload_conflict_message', {
                filename: upload.basename,
            });
            this.show = true;
        },

        openNext() {
            if (this.show && this.currentId) {
                return false;
            }

            while (this.queue.length > 0) {
                const nextId = this.queue[0];
                const next = this.getUploadById(nextId);

                if (!next || next.errorStatus !== 409) {
                    this.queue.shift();
                    continue;
                }

                this.open(next);
                return true;
            }

            return false;
        },

        resolve(strategy) {
            const currentId = this.currentId;
            const upload = this.getUploadById(currentId);

            if (this.applyToAll) {
                this.policy = strategy;

                this.uploads
                    .filter((item) => item.errorStatus === 409)
                    .forEach((item) => this.apply(item, strategy));

                this.queue = [];
                this.currentId = null;
                this.message = '';
                this.show = false;
            } else if (upload) {
                this.apply(upload, strategy);
                this.dequeue(currentId);
                this.currentId = null;
                this.message = '';

                const hasNext = this.openNext();

                if (!hasNext) {
                    this.show = false;
                }
            } else {
                this.show = false;
            }

            this.applyToAll = false;
        },

        apply(upload, strategy) {
            if (strategy === 'cancel') {
                upload.skip();
                this.$emit('resolved', upload, strategy);
                return;
            }

            upload.retry({
                option: strategy,
            }, {
                conflict: upload.conflict,
                resolution: strategy,
            });

            this.$emit('resolved', upload, strategy);
        },

        onOpenUpdated(open) {
            if (open) {
                this.show = true;
                return;
            }

            if (!this.show) {
                return;
            }

            this.resolve('cancel');
        },
    },
};
</script>
