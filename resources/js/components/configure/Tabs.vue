<template>
    <element-container @resized="containerWidth = $event.width">
        <div>
            <div v-for="tab in tabs" :key="tab.handle">
                <publish-sections
                    :sections="tab.sections"
                    :read-only="readOnly"
                    :syncable="syncable"
                    :heading="tab.display"
                    :description="tab.instructions"
                    @updated="(handle, value) => $emit('updated', handle, value)"
                    @meta-updated="(handle, value) => $emit('meta-updated', handle, value)"
                    @synced="$emit('synced', $event)"
                    @desynced="$emit('desynced', $event)"
                    @focus="$emit('focus', $event)"
                    @blur="$emit('blur', $event)"
                />
            </div>
        </div>
    </element-container>
</template>

<script>
export default {
    emits: ['updated', 'meta-updated', 'synced', 'desynced', 'focus', 'blur'],

    inject: ['store', 'storeName', 'wrapFieldsInCards'],

    props: {
        readOnly: Boolean,
        syncable: Boolean,
    },

    data() {
        return {
            active: this.store.blueprint.tabs[0].handle,
            containerWidth: null,
        };
    },

    computed: {
        tabs() {
            return this.store.blueprint.tabs;
        },

        actionsPortal() {
            return `publish-actions-${this.storeName}`;
        },
    },
};
</script>
