<template>
    <div>
        <RelationshipFieldtype
            v-bind="$props"
            :value="forms"
            @update:value="formsUpdated"
            @update:meta="$emit('update:meta', $event)"
            @focus="$emit('focus')"
            @blur="$emit('blur')"
        />

        <SubmissionsStack
            v-if="submissionsMeta"
            v-model:open="viewingSubmissions"
            :form="submissionsMeta.form"
            :action-url="submissionsMeta.actionUrl"
            :filters="submissionsMeta.filters"
        />

        <Stack v-if="configureMeta" v-model:open="configuring" size="half" :title="__('Configure')">
            <p
                class="mb-6 text-sm text-gray-600 dark:text-gray-300"
                v-text="__('messages.form_fieldtype_configure_instructions')"
            />

            <PublishContainer
                ref="configureContainer"
                v-model="configOverrides"
                v-model:modified-fields="modifiedOverrides"
                :blueprint="configureMeta.blueprint"
                :meta="configureMeta.meta"
                :origin-values="configureMeta.originValues"
                :origin-meta="configureMeta.originMeta"
                :sync-field-confirmation-text="__('messages.form_fieldtype_sync_confirmation')"
                as-config
            >
                <PublishTabs />
            </PublishContainer>

            <template #footer-end>
                <Button variant="ghost" :text="__('Cancel')" @click="configuring = false" />
                <Button variant="primary" :text="__('Apply')" @click="applyConfigure" />
            </template>
        </Stack>
    </div>
</template>

<script>
import clone from '@/util/clone.js';
import Fieldtype from './Fieldtype.vue';
import RelationshipFieldtype from './relationship/RelationshipFieldtype.vue';
import SubmissionsStack from '@/components/forms/SubmissionsStack.vue';
import { Button, PublishContainer, PublishTabs, Stack } from '@/components/ui';

export default {
    mixins: [Fieldtype],

    components: {
        RelationshipFieldtype,
        SubmissionsStack,
        PublishContainer,
        PublishTabs,
        Button,
        Stack,
    },

    provide() {
        return {
            formFieldtypeItem: {
                hasSubmissions: (item) => !!this.meta.submissions?.[item.id],
                hasConfigure: (item) => !!this.meta.configureMeta?.[item.id],
                viewSubmissions: (item) => (this.viewingSubmissionsFor = item.id),
                configure: (item) => (this.configuringFor = item.id),
            },
        };
    },

    data() {
        return {
            viewingSubmissionsFor: null,
            configuringFor: null,
            configOverrides: {},
            modifiedOverrides: [],
        };
    },

    computed: {
        forms() {
            return this.value?.forms ?? [];
        },

        formConfig() {
            return { ...(this.value?.config ?? {}) };
        },

        viewingSubmissions: {
            get() {
                return this.viewingSubmissionsFor !== null;
            },
            set(open) {
                if (!open) this.viewingSubmissionsFor = null;
            },
        },

        configuring: {
            get() {
                return this.configuringFor !== null;
            },
            set(open) {
                if (!open) this.configuringFor = null;
            },
        },

        submissionsMeta() {
            if (!this.forms.includes(this.viewingSubmissionsFor)) return null;

            return this.meta.submissions?.[this.viewingSubmissionsFor] ?? null;
        },

        configureMeta() {
            if (!this.forms.includes(this.configuringFor)) return null;

            return this.meta.configureMeta?.[this.configuringFor] ?? null;
        },

        replicatorPreview() {
            if (!this.showFieldPreviews) return;

            return this.forms.map((id) => {
                const item = this.meta.data.find((d) => d.id === id);
                return item ? item.title : id;
            });
        },

        internalFieldActions() {
            return [
                {
                    title: __('Unlink All'),
                    dangerous: true,
                    run: this.unlinkAll,
                    visible: this.forms.length > 0,
                },
            ];
        },
    },

    watch: {
        configuringFor(handle) {
            if (handle === null) {
                this.$refs.configureContainer?.clearDirtyState?.();
                return;
            }

            const config = clone(this.formConfig[handle] ?? {});

            this.configOverrides = { ...clone(this.meta.configureMeta[handle].originValues), ...config };
            this.modifiedOverrides = Object.keys(config);
        },
    },

    methods: {
        formsUpdated(forms) {
            const config = Object.fromEntries(forms.map((handle) => [handle, this.formConfig[handle] ?? {}]));

            this.update({ forms, config });
        },

        unlinkAll() {
            this.formsUpdated([]);
            this.updateMeta({ ...this.meta, data: [] });
        },

        applyConfigure() {
            const config = Object.fromEntries(
                Object.entries(this.configOverrides).filter(([handle]) => this.modifiedOverrides.includes(handle)),
            );

            this.update({ forms: this.forms, config: { ...this.formConfig, [this.configuringFor]: config } });
            this.configuringFor = null;
        },
    },
};
</script>
