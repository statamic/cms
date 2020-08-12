<template>

    <div class="card update-release mb-5">
        <div class="flex justify-between mb-3">
            <div>
                <h1>{{ release.version }}</h1>
                <h5 class="date" v-text="__('Released on :date', { date: release.date })" />
            </div>
            <div v-if="showActions">
                <button v-if="release.type === 'current'" class="btn opacity-50" disabled v-text="__('Current Version')" />
                <button v-else-if="release.latest" @click="$emit('install')" class="btn" v-text="__('Update to Latest')" />
                <button v-else @click="$emit('install')" class="btn">
                    <template v-if="release.type === 'upgrade'">{{ __('Update to :version', { version: release.version }) }}</template>
                    <template v-if="release.type === 'downgrade'">{{ __('Downgrade to :version', { version: release.version }) }}</template>
                </button>
            </div>
        </div>
        <div class="card-body">
            <div v-html="release.body"></div>
        </div>
    </div>

</template>

<script>
export default {

    props: {
        release: { type: Object, required: true },
        showActions: { type: Boolean }
    }

}
</script>
