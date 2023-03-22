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
        {{ tab.display }}

        <dropdown-list v-if="isActive" ref="dropdown" placement="bottom-start" class="text-left">
            <dropdown-item @click="edit" v-text="__('Edit')" />
            <dropdown-item @click="remove" class="warning" v-text="__('Delete')" />
        </dropdown-list>

        <confirmation-modal
            v-if="editing"
            :title="__('Edit Tab')"
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
    },

    data() {
        return {
            handle: this.tab.handle,
            display: this.tab.display,
            instructions: this.tab.instructions,
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

    },

    methods: {

        edit() {
            this.editing = true;
            setTimeout(() => this.$refs.title.focus(), 100); // better as an @opened event on the modal
        },

        editConfirmed() {
            this.$emit('updated', {
                ...this.tab,
                handle: this.handle,
                display: this.display,
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
        }

    }

}
</script>
