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
    >
        <div>
            <Header :title="title" icon="preferences">
                <ButtonGroup>
                    <Button type="submit" variant="primary" :text="__('Save')" @click="save" />

                    <Dropdown align="end" v-if="hasSaveAsOptions">
                        <template #trigger>
                            <Button icon="ui/chevron-down" variant="primary" />
                        </template>
                        <DropdownMenu>
                            <DropdownLabel>{{ __('Save to') }}...</DropdownLabel>
                            <DropdownItem
                                v-for="option in saveAsOptions"
                                :key="option.url"
                                :text="option.label"
                                @click="saveAs(option.url)"
                            />
                        </DropdownMenu>
                    </Dropdown>
                </ButtonGroup>
            </Header>

            <PublishTabs />
        </div>
    </PublishContainer>
</template>

<script>
import {
    Header,
    Button,
    ButtonGroup,
    Dropdown,
    DropdownMenu,
    DropdownItem,
    DropdownLabel,
    PublishContainer,
    PublishTabs,
} from '@statamic/ui';

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
    },

    created() {
        this.$keys.bindGlobal(['mod+s'], (e) => {
            e.preventDefault();
            this.save();
        });
    },

    watch: {
        saving(saving) {
            this.$progress.loading('preferences-edit-form', saving);
        },
    },
};
</script>
