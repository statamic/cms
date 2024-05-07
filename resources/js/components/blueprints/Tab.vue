<template>

    <button
        class="blueprint-tab tab-button"
        role="tab"
        :class="{ 'active': isActive }"
        :aria-controls="`tab-panel-${tab._id}`"
        :aria-selected="isActive"
        :id="`tab-${tab._id}`"
        :tabindex="isActive ? 0 : -1"
        @click="$emit('selected')"
        @mouseenter="$emit('mouseenter')"
    >
        <svg-icon v-if="tab.icon" :name="iconName(tab.icon)" :directory="iconBaseDirectory" class="w-4 h-4 rtl:ml-1 ltr:mr-1" />

        {{ __(tab.display) }}

        <dropdown-list v-if="isActive" ref="dropdown" placement="bottom-start" class="rtl:text-right ltr:text-left">
            <template #trigger>
                <button class="rtl:mr-2 ltr:ml-2 hover:text-gray-900 active:text-gray-900" :aria-label="__('Open Dropdown')">
                    <svg-icon name="micro/chevron-down-xs" class="w-2" />
                </button>
            </template>
            <dropdown-item @click="edit" v-text="__('Edit')" />
            <dropdown-item @click="remove" class="warning" v-text="__('Delete')" />
        </dropdown-list>

        <confirmation-modal
            v-if="editing"
            :title="editText"
            @opened="$refs.title.focus()"
            @confirm="editConfirmed"
            @cancel="editCancelled"
        >
            <div class="publish-fields @container">
                <div class="form-group w-full">
                    <label v-text="__('Title')" />
                    <input ref="title" type="text"
                        :value="display"
                        @input="fieldUpdated('display', $event.target.value)"
                        class="input-text" />
                </div>
                <div class="form-group w-full">
                    <label v-text="__('Handle')" />
                    <input type="text"
                        :value="handle"
                        @input="fieldUpdated('handle', $event.target.value)"
                        class="input-text font-mono text-sm" />
                </div>
                <div class="form-group w-full" v-if="showInstructions">
                    <label v-text="__('Instructions')" />
                    <input type="text"
                        :value="instructions"
                        @input="fieldUpdated('instructions', $event.target.value)"
                        class="input-text text-sm" />
                </div>

                <div class="form-group w-full" v-if="showInstructions">
                    <label v-text="__('Icon')" />
                    <publish-field-meta
                        :config="{ handle: 'icon', type: 'icon', directory: this.iconBaseDirectory, folder: this.iconSubFolder }"
                        :initial-value="icon"
                        v-slot="{ meta, value, loading }"
                    >
                        <icon-fieldtype v-if="!loading" handle="icon" :meta="meta" :value="value" @input="fieldUpdated('icon', $event)" />
                    </publish-field-meta>
                </div>
            </div>
        </confirmation-modal>
    </button>

</template>

<script>
export default {

    props: {
        tab: {
            type: Object,
            required: true,
        },
        currentTab: {
            type: String,
            required: true,
        },
        showInstructions: {
            type: Boolean,
            default: false,
        },
        editText: {
            type: String,
        }
    },

    data() {
        return {
            handle: this.tab.handle,
            display: this.tab.display,
            instructions: this.tab.instructions,
            icon: this.tab.icon,
            editing: false,
            handleSyncedWithDisplay: false,
        }
    },

    created() {
        // This logic isn't ideal, but it was better than passing along a 'isNew' boolean and having
        // to deal with stripping it out and making it not new, etc. Good enough for a quick win.
        if (!this.handle || this.handle == 'new_tab' || this.handle == 'new_set_group') {
            this.handleSyncedWithDisplay = true;
        }
    },

    computed: {

        isActive() {
            return this.currentTab === this.tab._id;
        },

        iconBaseDirectory() {
            return this.$config.get('setIconsDirectory');
        },

        iconSubFolder() {
            return this.$config.get('setIconsFolder');
        },

    },

    methods: {

        edit() {
            this.editing = true;
        },

        editConfirmed() {
            if (! this.handle) {
                this.handle = this.$slugify(this.display, '_');
            }

            this.$emit('updated', {
                ...this.tab,
                handle: this.handle,
                display: this.display,
                instructions: this.instructions,
                icon: this.icon,
            });

            this.editing = false;
        },

        editCancelled() {
            this.editing = false;
            this.handle = this.tab.handle;
            this.display = this.tab.display;
        },

        fieldUpdated(handle, value) {
            if (handle === 'display' && this.handleSyncedWithDisplay) {
                this.handle = this.$slugify(value, '_');
            }

            if (handle === 'handle') {
                this.handleSyncedWithDisplay = false;
            }

            this[handle] = value;
        },

        remove() {
            this.$emit('removed');
        },

        iconName(name) {
            if (! name) return null;

            return this.iconSubFolder
                ? this.iconSubFolder+'/'+name
                : name;
        },

    }

}
</script>
