<template>
    <element-container @resized="containerWidth = $event.width">
        <template #default>
            <div>
                <div v-for="tab in mainTabs" :key="tab.handle">
                    <div class="mb-2 content">
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

                <portal-target :name="actionsPortal" class="publish-tab publish-tab-actions-footer" />
            </div>
        </template>
    </element-container>
</template>

<script>
export default {
    emits: ['updated', 'meta-updated', 'synced', 'desynced', 'focus', 'blur'],

    inject: ['storeName'],

    props: {
        readOnly: Boolean,
        syncable: Boolean,
        enableSidebar: Boolean,
    },

    data() {
        const state = this.$store.state.publish[this.storeName];

        return {
            active: state.blueprint.tabs[0].handle,
            containerWidth: null
        }
    },

    computed: {
        state() {
            return this.$store.state.publish[this.storeName];
        },

        tabs() {
            return this.state.blueprint.tabs;
        },

        mainTabs() {
            // @todo(jsonvarga): Check if this is correct. It seems like that shouldShowSidebar always was false
            return this.tabs;
        },

        actionsPortal() {
            return `publish-actions-${this.storeName}`;
        }
    }
}
</script>
