<template>

    <div class="card update-release mb-5">
        <div class="flex justify-between mb-3">
            <div>
                <h1>{{ release.version }}</h1>
                <h5 class="date" v-text="__('Released on :date', { date: release.date })" />
            </div>
            <div v-if="showActions">
                <button v-if="release.type === 'current'" class="btn opacity-50" disabled v-text="__('Current Version')" />
                <button v-else-if="release.latest" @click="confirmationPrompt = release" class="btn" v-text="__('Update to Latest')" />
                <button v-else @click="confirmationPrompt = release" class="btn">
                    <template v-if="release.type === 'upgrade'">{{ __('Update to :version', { version: release.version }) }}</template>
                    <template v-if="release.type === 'downgrade'">{{ __('Downgrade to :version', { version: release.version }) }}</template>
                </button>
            </div>
        </div>
        <div class="card-body">
            <div v-html="release.body"></div>
        </div>

        <confirmation-modal
            v-if="confirmationPrompt"
            :title="packageName"
            :bodyText="__('Are you sure you want to :type to :version?', { type: confirmationPrompt.type, version: confirmationPrompt.version })"
            :buttonText="__('Confirm')"
            :danger="true"
            @confirm="$emit('install')"
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

}
</script>
