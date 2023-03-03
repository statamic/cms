<template>

    <div class="publish-sections">
        <div class="mb-5" v-for="(section, i) in sections" :key="i">
            <div class="card p-0 mb-5">
                <div class="p-5 border-b border-gray-400" v-if="section.display">
                    <label v-text="section.display" class="text-lg font-bold" />
                    <div class="help-block" v-if="section.instructions"><p v-html="section.instructions" /></div>
                </div>
                <publish-fields
                    :fields="section.fields"
                    :read-only="readOnly"
                    :syncable="syncable"
                    :can-toggle-labels="canToggleLabels"
                    @updated="(handle, value) => $emit('updated', handle, value)"
                    @meta-updated="(handle, value) => $emit('meta-updated', handle, value)"
                    @synced="$emit('synced', $event)"
                    @desynced="$emit('desynced', $event)"
                    @focus="$emit('focus', $event)"
                    @blur="$emit('blur', $event)"
                />
            </div>
        </div>
    </div>

</template>

<script>
export default {

    props: {
        sections: {
            type: Array,
            required: true
        },
        readOnly: Boolean,
        syncable: Boolean,
        syncableFields: Array,
        canToggleLabels: Boolean,
        namePrefix: String,
    }

}
</script>
