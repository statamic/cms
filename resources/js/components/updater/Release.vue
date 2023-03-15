<template>

    <div class="card update-release mb-10">
        <div class="flex justify-between mb-6">
            <div>
                <h1>{{ release.version }}</h1>
                <h5 class="date" v-text="__('Released on :date', { date: release.date })" />
            </div>
            <div v-if="showActions">
                <button v-if="release.type === 'current'" class="btn opacity-50" disabled v-text="__('Current Version')" />
            </div>
        </div>
        <div class="card-body">
            <div v-html="body"></div>
        </div>
    </div>

</template>

<script>
export default {

    props: {
        release: { type: Object, required: true },
        packageName: { type: String, required: true },
        showActions: { type: Boolean }
    },

    computed: {
        body() {
            return markdown(this.release.body)
                .replaceAll('[new]', '<span class="label" style="background: #5bc0de;">NEW</span>')
                .replaceAll('[fix]', '<span class="label" style="background: #5cb85c;">FIX</span>')
                .replaceAll('[break]', '<span class="label" style="background: #d9534f;">BREAK</span>')
                .replaceAll('[na]', '<span class="label" style="background: #e8e8e8;">N/A</span>')
        }
    },

}
</script>
