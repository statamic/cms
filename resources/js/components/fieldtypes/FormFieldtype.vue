<template>
    <div>
        <RelationshipFieldtype
            v-bind="$props"
            :value="form"
            @update:value="formUpdated"
            @update:meta="$emit('update:meta', $event)"
            @focus="$emit('focus')"
            @blur="$emit('blur')"
        />

        <Stack v-if="submissionsMeta" v-model:open="viewingSubmissions" :title="__('Submissions')">
            <SubmissionListing
                :form="submissionsMeta.form"
                :action-url="submissionsMeta.actionUrl"
                :filters="submissionsMeta.filters"
                view-in-stack
                sort-column="datestamp"
                sort-direction="desc"
                @view="viewingSubmission = $event"
            />

            <InlineSubmissionForm
                v-if="viewingSubmission"
                :url="viewingSubmission.url"
                @closed="viewingSubmission = null"
            />
        </Stack>

        <Stack v-if="configurationMeta" v-model:open="configuringForm" size="half" :title="__('Configure')">
            <p
                class="mb-6 text-sm text-gray-600 dark:text-gray-300"
                v-text="__('messages.form_fieldtype_configure_instructions')"
            />

            <PublishContainer
                v-model="configOverrides"
                v-model:modified-fields="modifiedOverrides"
                :blueprint="configurationMeta.blueprint"
                :meta="configurationMeta.meta"
                :origin-values="configurationMeta.originValues"
                :origin-meta="configurationMeta.originMeta"
                :sync-field-confirmation-text="__('messages.form_fieldtype_sync_confirmation')"
                :track-dirty-state="false"
                as-config
            >
                <PublishTabs />
            </PublishContainer>

            <template #footer-end>
                <Button variant="ghost" :text="__('Cancel')" @click="configuringForm = false" />
                <Button variant="primary" :text="__('Apply')" @click="applyConfigure" />
            </template>
        </Stack>
    </div>
</template>

<script>
import clone from '@/util/clone.js';
import Fieldtype from './Fieldtype.vue';
import RelationshipFieldtype from './relationship/RelationshipFieldtype.vue';
import InlineSubmissionForm from '@/components/forms/InlineSubmissionForm.vue';
import SubmissionListing from '@/components/forms/SubmissionListing.vue';
import { Button, PublishContainer, PublishTabs, Stack } from '@/components/ui';

export default {
    mixins: [Fieldtype],

    components: {
        RelationshipFieldtype,
        InlineSubmissionForm,
        SubmissionListing,
        PublishContainer,
        PublishTabs,
        Button,
        Stack,
    },

    provide() {
        return {
            formFieldtypeItem: {
                hasSubmissions: () => !!this.submissionsMeta,
                hasConfigure: () => !!this.configurationMeta,
                viewSubmissions: () => (this.viewingSubmissions = true),
                configure: () => (this.configuringForm = true),
            },
        };
    },

    data() {
        return {
            viewingSubmissions: false,
            viewingSubmission: null,
            configuringForm: false,
            configOverrides: {},
            modifiedOverrides: [],
        };
    },

    computed: {
        configurable() {
            return this.meta.configurable;
        },

        form() {
            return (this.configurable ? this.value?.form : this.value) ?? [];
        },

        submissionsMeta() {
            const submissions = this.meta.submissions;

            if (!submissions || !this.form.includes(submissions.form)) return null;

            return submissions;
        },

        configurationMeta() {
            const configuration = this.meta.configuration;

            if (!configuration || !this.form.includes(configuration.form)) return null;

            return configuration;
        },

        replicatorPreview() {
            if (!this.showFieldPreviews) return;

            return this.form.map((id) => {
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
                    visible: this.form.length > 0,
                },
            ];
        },
    },

    watch: {
        viewingSubmissions(open) {
            if (!open) this.viewingSubmission = null;
        },

        configuringForm(open) {
            if (!open) return;

            const config = clone(this.value?.config ?? {});

            this.configOverrides = { ...clone(this.configurationMeta.originValues), ...config };
            this.modifiedOverrides = Object.keys(config);
        },
    },

    methods: {
        formUpdated(ids) {
            if (!this.configurable) return this.update(ids);

            const config = ids[0] === this.form[0] ? this.value?.config : {};

            this.update({ form: ids, config: config ?? {} });
        },

        unlinkAll() {
            this.formUpdated([]);
            this.updateMeta({ ...this.meta, data: [] });
        },

        applyConfigure() {
            const config = Object.fromEntries(
                Object.entries(this.configOverrides).filter(([handle]) => this.modifiedOverrides.includes(handle)),
            );

            this.update({ form: this.form, config });
            this.configuringForm = false;
        },
    },
};
</script>
