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
                        :focus="true" />

                    <button
                        class="btn-primary w-full mb-3"
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
        canManagePublishState: Boolean,
    },

    data() {
        return {
            action: this.canManagePublishState ? 'publish' : 'revision',
            revisionMessage: null,
            saving: false,
        }
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

            options.push({ value: 'revision', label: __('Create Revision') });

            return options;
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
            const payload = { message: this.revisionMessage };

            this.$axios.post(this.actions.publish, payload).then(response => {
                this.$toast.success(__('Published'));
                this.revisionMessage = null;
                this.$emit('saved', { published: true, isWorkingCopy: false, response });
            }).catch(e => this.handleAxiosError(e));
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
            this.$toast.error(__('Something went wrong'));
        }

    }

}
</script>
