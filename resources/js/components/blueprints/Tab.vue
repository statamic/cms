<template>
    <TabTrigger :name="tab._id" class="blueprint-tab flex items-center">
        <Icon
            v-if="tab.icon"
            :name="tab.icon"
            :set="iconSet"
            class="h-4 w-4 me-1"
        />

        {{ __(tab.display) }}

        <Dropdown v-if="isActive" placement="left-start" class="me-3">
            <template #trigger>
                <button
                    class="hover:text-gray-900 active:text-gray-900 dark:hover:text-gray-400 ms-1"
                    :aria-label="__('Open Dropdown')"
                >
                    <Icon name="chevron-down" />
                </button>
            </template>
            <DropdownMenu>
                <DropdownItem :text="__('Edit')" icon="edit" @click="edit" />
                <DropdownItem :text="__('Delete')" icon="trash" variant="destructive" @click="remove" />
            </DropdownMenu>
        </Dropdown>

        <ui-stack
            narrow
            v-if="editing"
            @opened="() => $nextTick(() => $refs.title.focus())"
            @closed="editCancelled"
        >
            <div class="h-full overflow-scroll overflow-x-auto bg-white px-6 dark:bg-dark-800">
                <header class="py-2 -mx-6 px-6 border-b border-gray-200 dark:border-gray-700 mb-5">
                    <div class="flex items-center justify-between">
                        <ui-heading size="lg">
                            {{ editText }}
                        </ui-heading>
                        <ui-button icon="x" variant="ghost" class="-me-2" @click="editCancelled" />
                    </div>
                </header>
                <div class="space-y-6">
                    <Field :label="__('Title')" class="form-group field-w-100">
                        <Input ref="title" :model-value="display" @update:model-value="fieldUpdated('display', $event)" />
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
                            set: iconSet,
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
                    <div class="py-6 space-x-2 -mx-6 px-6 border-t border-gray-200 dark:border-gray-700">
                        <ui-button :text="isSoloNarrowStack ? __('Save') : __('Confirm')" @click="handleSaveOrConfirm" variant="primary" />
                        <ui-button :text="__('Cancel')" @click="editCancelled" variant="ghost" />
                    </div>
                </div>
            </div>
        </ui-stack>
    </TabTrigger>
</template>

<script>
import { TabTrigger, Dropdown, DropdownMenu, DropdownItem, Icon, Field, Input } from '@/components/ui';

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
            saveKeyBinding: null,
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

        iconSet() {
            return this.$config.get('replicatorSetIcons') || undefined;
        },

        isSoloNarrowStack() {
            const stacks = this.$stacks.stacks();
            return stacks.length === 1 && stacks[0]?.data?.vm?.narrow === true;
        },
    },

    watch: {
        editing: {
            handler(isEditing) {
                if (isEditing) {
                    // Bind Cmd+S to trigger save or confirm based on stack type
                    this.saveKeyBinding = this.$keys.bindGlobal(['mod+s'], (e) => {
                        e.preventDefault();
                        e.stopPropagation();
                        this.handleSaveOrConfirm();
                    });
                } else {
                    // Unbind when stack is closed
                    if (this.saveKeyBinding) {
                        this.saveKeyBinding.destroy();
                        this.saveKeyBinding = null;
                    }
                }
            },
            immediate: false,
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

        handleSaveOrConfirm() {
            if (this.isSoloNarrowStack) {
                this.editAndSave();
            } else {
                this.editConfirmed();
            }
        },

        editAndSave() {
            // First confirm the tab changes
            this.editConfirmed();
            
            // Then trigger the blueprint save
            this.$nextTick(() => {
                this.$events.$emit('root-form-save');
            });
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
    },

    beforeUnmount() {
        if (this.saveKeyBinding) {
            this.saveKeyBinding.destroy();
        }
    },
};
</script>
