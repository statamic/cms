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

        <Stack v-if="configureMeta" v-model:open="configuringForm" size="half" :title="__('Configure')">
            <p
                class="mb-6 text-sm text-gray-600 dark:text-gray-300"
                v-text="__('messages.form_fieldtype_configure_instructions')"
            />

            <PublishContainer
                v-model="configOverrides"
                :blueprint="configureMeta.blueprint"
                :meta="configureMeta.meta"
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
                hasConfigure: () => !!this.configureMeta,
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
        };
    },

    computed: {
        configurable() {
            return this.config.max_items === 1;
        },

        form() {
            return (this.configurable ? this.value?.form : this.value) ?? [];
        },

        submissionsMeta() {
            const submissions = this.meta.submissions;

            if (!submissions || !this.form.includes(submissions.form)) return null;

            return submissions;
        },

        configureMeta() {
            const configure = this.meta.configure;

            if (!configure || !this.form.includes(configure.form)) return null;

            return configure;
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
            if (open) this.configOverrides = clone(this.value?.config ?? {});
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
            this.update({ form: this.form, config: this.configOverrides });
            this.configuringForm = false;
        },
    },
};
</script>
