<template>
    <PublishContainer
        ref="container"
        :name="name"
        :blueprint="blueprint"
        v-model="currentValues"
        reference="collection"
        :meta="meta"
        :errors="errors"
        :read-only="readOnly"
        as-config
    >
        <div>
            <Header :title="title" icon="preferences">
                <ButtonGroup role="group" aria-label="Save options">
                    <Button
                        type="submit"
                        variant="primary"
                        :text="__('Save')"
                        @click="save"
                        :aria-describedby="hasSaveAsOptions ? 'save-options-description' : undefined"
                    />

                    <Dropdown
                        align="end"
                        v-if="hasSaveAsOptions"
                        :aria-label="__('Additional save options')"
                        @open="onDropdownOpen"
                        @close="onDropdownClose"
                    >
                        <template #trigger>
                            <Button
                                icon="ui/chevron-down"
                                variant="primary"
                                :aria-label="__('Open save options menu')"
                                :aria-expanded="isDropdownOpen"
                                :aria-haspopup="true"
                                :aria-describedby="'save-options-description'"
                                @click="toggleDropdown"
                            />
                        </template>
                        <DropdownMenu
                            role="menu"
                            :aria-labelledby="'save-options-label'"
                        >
                            <DropdownLabel id="save-options-label">{{ __('Save to') }}...</DropdownLabel>
                            <DropdownItem
                                v-for="option in saveAsOptions"
                                :key="option.url"
                                :text="option.label"
                                @click="saveAs(option.url)"
                                role="menuitem"
                                :aria-label="`${__('Save to')} ${option.label}`"
                            />
                        </DropdownMenu>
                    </Dropdown>
                </ButtonGroup>

                <div
                    v-if="hasSaveAsOptions"
                    id="save-options-description"
                    class="sr-only"
                >
                    {{ __('Press enter to access additional save options') }}
                </div>
            </Header>

            <PublishTabs />
        </div>
    </PublishContainer>
</template>

<script>
import { Header, Button, ButtonGroup, Dropdown, DropdownMenu, DropdownItem, DropdownLabel, PublishContainer, PublishTabs } from '@/components/ui';

export default {
    components: {
        Header,
        Button,
        ButtonGroup,
        Dropdown,
        DropdownMenu,
        DropdownItem,
        DropdownLabel,
        PublishContainer,
        PublishTabs,
    },

    props: {
        blueprint: { required: true, type: Object },
        meta: { required: true, type: Object },
        values: { required: true, type: Object },
        title: { required: true, type: String },
        name: { type: String, default: 'base' },
        action: String,
        readOnly: { type: Boolean, default: false },
        reloadOnSave: { type: Boolean, default: false },
        saveAsOptions: { type: Array, default: () => [] },
    },

    data() {
        return {
            saving: false,
            currentValues: this.values,
            error: null,
            errors: {},
            hasSidebar: this.blueprint.tabs.map((tab) => tab.handle).includes('sidebar'),
            isDropdownOpen: false,
        };
    },

    computed: {
        hasSaveAsOptions() {
            return this.saveAsOptions.length;
        },

        isDirty() {
            return this.$dirty.has(this.name);
        },
    },

    methods: {
        clearErrors() {
            this.error = null;
            this.errors = {};
        },

        save() {
            this.saveAs(this.action);
        },

        saveAs(url) {
            this.saving = true;
            this.clearErrors();
            this.isDropdownOpen = false;

            this.$axios
                .patch(url, this.currentValues)
                .then(() => {
                    this.$refs.container.saved();
                    this.$nextTick(() => location.reload());
                })
                .catch((e) => this.handleAxiosError(e));
        },

        handleAxiosError(e) {
            this.saving = false;
            if (e.response && e.response.status === 422) {
                const { message, errors } = e.response.data;
                this.error = message;
                this.errors = errors;
                this.$toast.error(message);
            } else {
                const message = data_get(e, 'response.data.message');
                this.$toast.error(message || e);
                console.log(e);
            }
        },

        toggleDropdown() {
            this.isDropdownOpen = !this.isDropdownOpen;
        },

        onDropdownOpen() {
            this.isDropdownOpen = true;
        },

        onDropdownClose() {
            this.isDropdownOpen = false;
        },
    },

    created() {
        this.$keys.bindGlobal(['mod+s'], (e) => {
            e.preventDefault();
            this.save();
        });

        // Add keyboard navigation for dropdown
        this.$keys.bindGlobal(['escape'], (e) => {
            if (this.isDropdownOpen) {
                e.preventDefault();
                this.isDropdownOpen = false;
            }
        });
    },

    watch: {
        saving(saving) {
            this.$progress.loading('preferences-edit-form', saving);
        },
    },
};
</script>

<style scoped>
.sr-only {
    position: absolute;
    width: 1px;
    height: 1px;
    padding: 0;
    margin: -1px;
    overflow: hidden;
    clip: rect(0, 0, 0, 0);
    white-space: nowrap;
    border: 0;
}
</style>
