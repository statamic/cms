<template>

    <stack narrow name="publish-options" @closed="$emit('closed')">
        <div slot-scope="{ close }" class="bg-white dark:bg-dark-800 h-full flex flex-col">
            <publish-container name="revision-publish-form">
                <div>
                    <div class="bg-gray-200 dark:bg-dark-600 px-6 py-2 border-b border-gray-300 dark:border-dark-900 text-lg font-medium flex items-center justify-between">
                        {{ __('Publish') }}
                        <button
                            type="button"
                            class="btn-close"
                            @click="close"
                            v-html="'&times'" />
                    </div>

                    <div class="flex-1 overflow-auto p-6">

                        <div class="flex h-full items-center justify-center loading" v-if="saving">
                            <loading-graphic text="" />
                        </div>

                        <template v-else>

                            <select-input
                                class="mb-6"
                                v-model="action"
                                :options="options"
                            />

                            <div v-if="action">

                                <date-fieldtype
                                    v-if="action == 'schedule'"
                                    class="mb-6"
                                    name="publishTime"
                                    :value="publishTime" />

                                <date-fieldtype
                                    v-if="action == 'publish_later'"
                                    class="mb-6"
                                    :config="config"
                                    handle="publishLaterDateTime"
                                    :value="publishRevisionAt" />

                                <textarea-input
                                    class="mb-6 text-sm"
                                    v-model="revisionMessage"
                                    :placeholder="__('Notes about this revision')"
                                    @keydown.enter="submit"
                                    :focus="true" />

                                <button
                                    class="btn-primary w-full mb-6"
                                    v-text="submitButtonText"
                                    @click="submit"
                                />

                                <div class="text-gray text-xs flex mb-6">
                                    <div class="pt-px w-4 rtl:ml-2 ltr:mr-2">
                                        <svg-icon name="info-circle" class="pt-px" />
                                    </div>
                                    <div class="flex-1" v-text="actionInfoText" />
                                </div>

                                <div class="text-gray text-xs flex mb-6 text-red-500" v-if="action === 'schedule'">
                                    <div class="pt-px w-4 rtl:ml-2 ltr:mr-2">
                                        <svg-icon name="info-circle" class="pt-px" />
                                    </div>
                                    <div class="flex-1" v-text="__('messages.publish_actions_current_becomes_draft_because_scheduled')" />
                                </div>

                            </div>

                        </template>

                    </div>
                </div>
            </publish-container>
        </div>
    </stack>

</template>

<script>
export default {

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
            config: {
                earliest_date: { date: null, time: null},
                latest_date: { date: null, time: null},
                mode: 'single',
                type: 'single',
                inline: false,
                time_enabled: true,
                default: 'now',
                rows: 1,
                columns: 1
            },
            publishRevisionAt: { date: '2025-02-28', time: null},
            revisionMessage: null,
            saving: false,
        }
    },

    computed: {

        options() {
            const options = [];

            if (this.canManagePublishState) {
                options.push({ value: 'publish', label: __('Publish Now') });
                options.push({ value: 'publish_later', label: __('Publish Later') });

                if (this.published) {
                    options.push({ value: 'unpublish', label: __('Unpublish') });
                }
            }

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
            return _.findWhere(this.options, { value: this.action }).label;
        }

    },

    methods: {

        submit() {
            this.saving = true;
            this.$emit('saving');
            const method = 'submit'+this.action.charAt(0).toUpperCase()+this.action.substring(1);
            this[method]();
        },

        submitPublish() {
            this.runBeforePublishHook();
        },

        runBeforePublishHook() {
            Statamic.$hooks
                .run('entry.publishing', { collection: this.collection, message: this.revisionMessage, storeName: this.publishContainer })
                .then(this.performPublishRequest)
                .catch(error => {
                    this.saving = false;
                    this.$toast.error(error || __('Something went wrong'));
                });
        },

        performPublishRequest() {
            const payload = { message: this.revisionMessage };
            this.$axios.post(this.actions.publish, payload)
                .then(response => {
                    this.saving = false;

                    if (! response.data.saved) {
                        this.$emit('failed');
                        return this.$toast.error(__(`Couldn't publish entry`));
                    }
                    this.$toast.success(__('Published'));
                    this.runAfterPublishHook(response);
                }).catch(error => this.handleAxiosError(error));
        },

        runAfterPublishHook(response) {
            // Once the publish request has completed, we want to run the "after" hook.
            // Devs can do what they need and we'll wait for them, but they can't cancel anything.
            Statamic.$hooks
                .run('entry.published', {
                    collection: this.collection,
                    reference: this.reference,
                    message: this.revisionMessage,
                    response
                })
                .then(() => {
                    // Finally, we'll emit an event. We need to wait until after the hooks are resolved because
                    // if this form is being shown in a stack, we only want to close it once everything's done.
                    this.revisionMessage = null;
                    this.$emit('saved', { published: true, isWorkingCopy: false, response });
                }).catch(e => {});
        },

        submitSchedule() {
            // todo
        },

        submitUnpublish() {
            const payload = { message: this.revisionMessage };

            this.$axios.post(this.actions.unpublish, { data: payload }).then(response => {
                this.saving = false;

                if (! response.data.saved) {
                    this.$emit('failed');
                    return this.$toast.error(__(`Couldn't unpublish entry`));
                }

                this.$toast.success(__('Unpublished'));
                this.revisionMessage = null;
                this.$emit('saved', { published: false, isWorkingCopy: false, response });
            }).catch(e => this.handleAxiosError(e));
        },

        submitRevision() {
            const payload = { message: this.revisionMessage };

            this.$axios.post(this.actions.createRevision, payload).then(response => {
                this.$toast.success(__('Revision created'));
                this.revisionMessage = null;
                this.$emit('saved', { isWorkingCopy: true, response });
            }).catch(e => this.handleAxiosError(e));
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
        }

    }

}
</script>
