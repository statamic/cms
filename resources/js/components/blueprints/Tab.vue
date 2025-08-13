<template>
    <TabTrigger :name="tab._id">
        <Icon
            v-if="tab.icon"
            :name="iconName(tab.icon)"
            :directory="iconBaseDirectory"
            class="h-4 w-4 ltr:mr-1 rtl:ml-1"
        />

        {{ __(tab.display) }}

        <Dropdown v-if="isActive" placement="left-start" class="me-3">
            <template #trigger>
                <button
                    class="hover:text-gray-900 active:text-gray-900 dark:hover:text-gray-400 ms-1"
                    :aria-label="__('Open Dropdown')"
                >
                    <Icon name="ui/chevron-down" />
                </button>
            </template>
            <DropdownMenu>
                <DropdownItem :text="__('Edit')" icon="edit" @click="edit" />
                <DropdownItem :text="__('Delete')" icon="trash" variant="destructive" @click="remove" />
            </DropdownMenu>
        </Dropdown>

        <confirmation-modal
            v-if="editing"
            :title="editText"
            @opened="() => {
                this.$nextTick(() => {
                    this.$refs.title.focus()
                });
            }"
            @confirm="editConfirmed"
            @cancel="editCancelled"
        >
            <div class="publish-fields">
                <Field :label="__('Title')" class="form-group field-w-100">
                    <Input ref="title" autofocus :model-value="display" @update:model-value="fieldUpdated('display', $event)" />
                </Field>

                <Field :label="__('Handle')" class="form-group field-w-100">
                    <Input class="font-mono" :model-value="handle" @update:model-value="fieldUpdated('handle', $event)" />
                </Field>

                <Field v-if="showInstructions" :label="__('Instructions')" class="form-group field-w-100">
                    <Input :model-value="instructions" @update:model-value="fieldUpdated('instructions', $event)" />
                </Field>

                <Field v-if="showInstructions" :label="__('Icon')" class="form-group field-w-100">
                    <publish-field-meta
                        :config="{
                            handle: 'icon',
                            type: 'icon',
                            directory: this.iconBaseDirectory,
                            folder: this.iconSubFolder,
                        }"
                        :initial-value="icon"
                        v-slot="{ meta, value, loading, config }"
                    >
                        <icon-fieldtype
                            v-if="!loading"
                            handle="icon"
                            :config="config"
                            :meta="meta"
                            :value="value"
                            @update:value="fieldUpdated('icon', $event)"
                        />
                    </publish-field-meta>
                </Field>
            </div>
        </confirmation-modal>
    </TabTrigger>
</template>

<script>
import { TabTrigger, Dropdown, DropdownMenu, DropdownItem, Icon, Field, Input } from '@statamic/ui';

export default {
    components: { TabTrigger, Dropdown, DropdownMenu, DropdownItem, Icon, Field, Input },

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
        },
    },

    data() {
        return {
            handle: this.tab.handle,
            display: this.tab.display,
            instructions: this.tab.instructions,
            icon: this.tab.icon,
            editing: false,
            handleSyncedWithDisplay: false,
        };
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
            if (!this.handle) {
                this.handle = snake_case(this.display);
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
                this.handle = snake_case(value);
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
            if (!name) return null;

            return this.iconSubFolder ? this.iconSubFolder + '/' + name : name;
        },
    },
};
</script>
