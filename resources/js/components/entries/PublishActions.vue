<template>

    <stack narrow name="publish-options" @closed="$emit('closed')">
        <div slot-scope="{ close }" class="bg-white h-full flex flex-col">

            <div class="bg-grey-20 px-3 py-1 border-b border-grey-30 text-lg font-medium flex items-center justify-between">
                {{ __('Publish') }}
                <button
                    type="button"
                    class="btn-close"
                    @click="close"
                    v-html="'&times'" />
            </div>

            <div class="flex-1 overflow-auto p-3">

                <div class="flex h-full items-center justify-center loading" v-if="saving">
                    <loading-graphic text="" />
                </div>

                <select-input
                    class="mb-3"
                    v-model="action"
                    :options="options"
                />

                <div v-if="action">

                    <date-fieldtype
                        v-if="action == 'schedule'"
                        class="mb-3"
                        name="publishTime"
                        :value="publishTime" />

                    <textarea-input
                        class="mb-3 text-sm"
                        v-model="revisionMessage"
                        :placeholder="__('Notes about this revision')"
                        @keydown.enter="submit"
                        autofocus />

                    <button
                        class="btn btn-primary w-full mb-3"
                        v-text="submitButtonText"
                        @click="submit"
                    />

                    <div class="text-grey text-xs flex mb-3">
                        <div class="pt-px w-4 mr-1">
                            <svg-icon name="info-circle" class="pt-px" />
                        </div>
                        <div class="flex-1" v-text="actionInfoText" />
                    </div>

                    <div class="text-grey text-xs flex mb-3 text-red" v-if="action === 'schedule'">
                        <div class="pt-px w-4 mr-1">
                            <svg-icon name="info-circle" class="pt-px" />
                        </div>
                        <div class="flex-1">
                            Since the current revision is published and you've selected a date in the future, once you submit, the revision will act like a draft until the selected date.
                        </div>
                    </div>

                </div>

            </div>
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
    },

    data() {
        return {
            action: 'publish',
            revisionMessage: null,
            saving: false,
        }
    },

    computed: {

        options() {
            let options = [
                { value: 'publish', label: 'Publish Now', },
            ];

            if (this.published) {
                options.push({ value: 'unpublish', label: 'Unpublish' });
            }

            return options.concat([
                { value: 'revision', label: 'Create Revision', },
            ]);
        },

        actionInfoText() {
            switch (this.action) {
                case 'publish':
                    return `Changes to the working copy will applied to the entry and it will be published immediately.`;
                case 'schedule':
                    return `Changes to the working copy will applied to the entry and it will be appear published on the selected date.`;
                case 'unpublish':
                    return `The current revision will be unpublished.`;
                case 'revision':
                    return `A revision will be created based off the working copy. The current revision will not change.`;
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
                .run('entry.publishing', { collection: this.collection, message: this.revisionMessage })
                .then(this.performPublishRequest)
                .catch(error => {
                    this.saving = false;
                    this.$toast.error(error || 'Something went wrong');
                });
        },

        performPublishRequest() {
            const payload = { message: this.revisionMessage };
            this.$axios.post(this.actions.publish, payload)
                .then(response => {
                    this.saving = false;
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
            this.saving = false;
            this.$toast.error(e || 'Something went wrong');
        }

    }

}
</script>
