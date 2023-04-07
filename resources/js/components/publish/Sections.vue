<template>
    <div class="publish-sections @container">
        <div class="mb-8" v-for="(section, i) in sections" :key="i">
            <div class="card p-0">
                <header class="publish-section-header" v-if="section.display">
                    <label v-text="section.display" class="text-base font-semibold" />
                    <div class="help-block" v-if="section.instructions"><p v-html="$options.filters.markdown(section.instructions)" /></div>
                </header>
                <publish-fields
                    :fields="section.fields"
                    :read-only="readOnly"
                    :syncable="syncable"
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
        namePrefix: String,
    }

}
</script>
