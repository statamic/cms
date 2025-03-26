<template>
    <element-container @resized="containerWidth = $event.width">
        <div>
            <div v-for="tab in tabs" :key="tab.handle">
                <div class="content mb-2">
                    <h2 v-text="tab.display" class="text-base" />
                    <p v-html="tab.instructions" />
                </div>
                <div class="">
                    <publish-sections
                        :sections="tab.sections"
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
    </element-container>
</template>

<script>
export default {
    emits: ['updated', 'meta-updated', 'synced', 'desynced', 'focus', 'blur'],

    inject: ['store', 'storeName'],

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
