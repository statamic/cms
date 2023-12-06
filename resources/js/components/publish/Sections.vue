<template>
    <div class="publish-sections">
        <div class="publish-sections-section" v-for="(section, i) in sections" :key="i">
            <div class="card p-0">
                <header class="publish-section-header @container" v-if="section.display">
                    <div class="publish-section-header-inner">
                        <label v-text="__(section.display)" class="text-base font-semibold" />
                        <div class="help-block" v-if="section.instructions"><p v-html="$options.filters.markdown(__(section.instructions))" /></div>
                    </div>
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
