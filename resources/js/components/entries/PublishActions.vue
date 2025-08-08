<template>
    <stack narrow name="publish-options" @closed="$emit('closed')" v-slot="{ close }">
        <div class="m-2 flex h-full flex-col rounded-xl bg-white dark:bg-gray-800">
            <header
                class="flex items-center justify-between rounded-t-xl border-b border-gray-300 bg-gray-50 px-4 py-2 dark:border-gray-950 dark:bg-gray-900"
            >
                <Heading size="lg">{{ __('Publish') }}</Heading>
                <Button icon="x" variant="ghost" class="-me-2" @click="close" />
            </header>

            <div class="flex-1 overflow-auto">
                <div class="loading flex h-full items-center justify-center" v-if="saving">
                    <Icon name="loading" />
                </div>

                <div class="p-3 flex flex-col space-y-6" v-else>
                    <Select class="w-full" :options v-model="action" />

                    <template v-if="action">
<!--                        <DatePicker-->
<!--                            v-if="action == 'schedule'"-->
<!--                            v-model="publishTime"-->
<!--                        />-->

                        <Textarea
                            class="text-sm"
                            v-model="revisionMessage"
                            :placeholder="__('Notes about this revision')"
                            @keydown.enter="submit"
                            :focus="true"
                        />

                        <Button variant="primary" :text="submitButtonText" @click="submit" />

                        <div class="flex">
                            <Icon name="info" class="size-4 shrink-0 me-2" />
                            <Subheading size="sm" class="flex-1" :text="actionInfoText" />
                        </div>

                        <div class="flex text-red-500" v-if="action === 'schedule'">
                            <Icon name="info" class="size-4 shrink-0 me-2" />
                            <Subheading size="sm" class="flex-1 text-red-500" :text="__('messages.publish_actions_current_becomes_draft_because_scheduled')" />
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </stack>
</template>

<script>
import { Heading, Button, Select, DatePicker, Textarea, Icon, Subheading } from '@statamic/ui';

export default {
    components: { Heading, Button, Select, DatePicker, Textarea, Icon, Subheading },

    props: {
        actions: Object,
        published: Boolean,
        collection: String,
        reference: String,
        publishContainer: String,
        canManagePublishState: Boolean,
    },

    data() {
        return {
            action: this.canManagePublishState ? 'publish' : 'revision',
            revisionMessage: null,
            saving: false,
        };
    },

    computed: {
        options() {
            const options = [];

            if (this.canManagePublishState) {
                options.push({ value: 'publish', label: __('Publish Now') });

                if (this.published) {
                    options.push({ value: 'unpublish', label: __('Unpublish') });
                }
            }

            // options.push({ value: 'schedule', label: __('Schedule') });

            options.push({ value: 'revision', label: __('Create Revision') });

            return options;
        },

        actionInfoText() {
            switch (this.action) {
                case 'publish':
                    return __('messages.publish_actions_publish');
                case 'schedule':
                    return __('messages.publish_actions_schedule');
                case 'unpublish':
                    return __('messages.publish_actions_unpublish');
                case 'revision':
                    return __('messages.publish_actions_create_revision');
            }
        },

        submitButtonText() {
            return this.options.find((o) => o.value === this.action).label;
        },
    },

    methods: {
        submit() {
            this.saving = true;
            this.$emit('saving');
            const method = 'submit' + this.action.charAt(0).toUpperCase() + this.action.substring(1);
            this[method]();
        },

        submitPublish() {
            this.runBeforePublishHook();
        },

        runBeforePublishHook() {
            Statamic.$hooks
                .run('entry.publishing', {
                    collection: this.collection,
                    message: this.revisionMessage,
                })
                .then(this.performPublishRequest)
                .catch((error) => {
                    this.saving = false;
                    this.$toast.error(error || __('Something went wrong'));
                });
        },

        performPublishRequest() {
            const payload = { message: this.revisionMessage };
            this.$axios
                .post(this.actions.publish, payload)
                .then((response) => {
                    this.saving = false;

                    if (!response.data.saved) {
                        this.$emit('failed');
                        return this.$toast.error(__(`Couldn't publish entry`));
                    }
                    this.$toast.success(__('Published'));
                    this.runAfterPublishHook(response);
                })
                .catch((error) => this.handleAxiosError(error));
        },

        runAfterPublishHook(response) {
            // Once the publish request has completed, we want to run the "after" hook.
            // Devs can do what they need and we'll wait for them, but they can't cancel anything.
            Statamic.$hooks
                .run('entry.published', {
                    collection: this.collection,
                    reference: this.reference,
                    message: this.revisionMessage,
                    response,
                })
                .then(() => {
                    // Finally, we'll emit an event. We need to wait until after the hooks are resolved because
                    // if this form is being shown in a stack, we only want to close it once everything's done.
                    this.revisionMessage = null;
                    this.$emit('saved', { published: true, isWorkingCopy: false, response });
                })
                .catch((e) => {});
        },

        submitSchedule() {
            // todo
        },

        submitUnpublish() {
            const payload = { message: this.revisionMessage };

            this.$axios
                .post(this.actions.unpublish, { data: payload })
                .then((response) => {
                    this.saving = false;

                    if (!response.data.saved) {
                        this.$emit('failed');
                        return this.$toast.error(__(`Couldn't unpublish entry`));
                    }

                    this.$toast.success(__('Unpublished'));
                    this.revisionMessage = null;
                    this.$emit('saved', { published: false, isWorkingCopy: false, response });
                })
                .catch((e) => this.handleAxiosError(e));
        },

        submitRevision() {
            const payload = { message: this.revisionMessage };

            this.$axios
                .post(this.actions.createRevision, payload)
                .then((response) => {
                    this.$toast.success(__('Revision created'));
                    this.revisionMessage = null;
                    this.$emit('saved', { isWorkingCopy: true, response });
                })
                .catch((e) => this.handleAxiosError(e));
        },

        handleAxiosError(e) {
            if (e.response && e.response.status === 422) {
                const { message, errors } = e.response.data;
                this.error = message;
                this.errors = errors;
                this.$toast.error(message);
                this.$reveal.invalid();
            } else if (e.response) {
                this.$toast.error(e.response.data.message);
            } else {
                this.$toast.error(e || 'Something went wrong');
            }

            this.saving = false;
            this.$emit('failed');
        },
    },
};
</script>
