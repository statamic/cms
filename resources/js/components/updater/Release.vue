<template>

    <div class="card update-release mb-5">
        <div class="flex justify-between mb-3">
            <div>
                <h1>{{ release.version }}</h1>
                <h5 class="date" v-text="__('Released on :date', { date: release.date })" />
            </div>
            <div v-if="!release.canUpdate">
                <button class="btn opacity-50" disabled v-text="__('Manual upgrade required')" />
            </div>
            <div v-if="release.canUpdate && showActions">
                <button v-if="release.type === 'current'" class="btn opacity-50" disabled v-text="__('Current Version')" />
                <button v-else-if="release.latest" @click="confirmationPrompt = release" class="btn" v-text="__('Update to Latest')" />
                <button v-else @click="confirmationPrompt = release" class="btn">
                    <template v-if="release.type === 'upgrade'">{{ __('Update to :version', { version: release.version }) }}</template>
                    <template v-if="release.type === 'downgrade'">{{ __('Downgrade to :version', { version: release.version }) }}</template>
                </button>
            </div>
        </div>
        <div class="card-body">
            <div v-html="body"></div>
        </div>

        <confirmation-modal
            v-if="confirmationPrompt"
            :title="confirmationTitle"
            :bodyText="confirmationText"
            :buttonText="__('Confirm')"
            :danger="true"
            @confirm="confirm"
            @cancel="confirmationPrompt = null"
        >
        </confirmation-modal>
    </div>

</template>

<script>
export default {

    props: {
        release: { type: Object, required: true },
        packageName: { type: String, required: true },
        showActions: { type: Boolean }
    },

    data() {
        return {
            confirmationPrompt: null,
        }
    },

    computed: {
        confirmationTitle() {
            let attrs = { name: this.packageName }

            return this.confirmationPrompt.type === 'downgrade'
                ? __('Downgrade :name', attrs)
                : __('Update :name', attrs)
        },

        confirmationText() {
            let attrs = { version: this.confirmationPrompt.version }

            return this.confirmationPrompt.type === 'downgrade'
                ? __('Are you sure you want to downgrade to :version?', attrs)
                : __('Are you sure you want to update to :version?', attrs)
        },

        body() {
            return markdown(this.release.body)
                .replaceAll('[new]', '<span class="label" style="background: #5bc0de;">NEW</span>')
                .replaceAll('[fix]', '<span class="label" style="background: #5cb85c;">FIX</span>')
                .replaceAll('[break]', '<span class="label" style="background: #d9534f;">BREAK</span>')
                .replaceAll('[na]', '<span class="label" style="background: #e8e8e8;">N/A</span>')
        }
    },

    methods: {

        confirm() {
            this.confirmationPrompt = null;
            this.$nextTick(() => this.$emit('install'));
        }

    }

}
</script>
