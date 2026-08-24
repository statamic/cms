<template>
    <Listing
        ref="listing"
        :url="requestUrl"
        :columns="columns"
        :action-url="actionUrl"
        :action-context="{ form }"
        :sort-column="sortColumn"
        :sort-direction="sortDirection"
        :preferences-prefix="preferencesPrefix"
        :filters="filters"
        :allow-presets="false"
        :push-query="!viewInStack"
    >
        <template #cell-datestamp="{ row: submission, value, isColumnVisible }">
            <component
                :is="viewInStack ? 'a' : 'Link'"
                class="title-index-field"
                :href="submission.url"
                @click.stop="view($event, submission)"
            >
                <SubmissionStatusIndicator v-if="!isColumnVisible('status')" :status="submission.status" />
                <span><date-time :of="value" /></span>
            </component>
        </template>
        <template #cell-status="{ row: submission }">
            <SubmissionStatusIndicator :status="submission.status" show-label :show-dot="false" />
        </template>
        <template #prepended-row-actions="{ row: submission }">
            <DropdownItem
                :text="__('View')"
                icon="eye"
                :href="viewInStack ? undefined : submission.url"
                @click="view($event, submission)"
            />
        </template>
    </Listing>
</template>

<script>
import { Listing, DropdownItem } from '@/components/ui';
import { Link } from '@inertiajs/vue3';
import SubmissionStatusIndicator from '@/components/forms/SubmissionStatusIndicator.vue';

export default {
    components: { SubmissionStatusIndicator, DropdownItem, Link, Listing },

    props: {
        form: String,
        actionUrl: String,
        sortColumn: String,
        sortDirection: String,
        columns: Array,
        filters: Array,
        viewInStack: { type: Boolean, default: false },
    },

    emits: ['view'],

    data() {
        return {
            preferencesPrefix: `forms.${this.form}`,
            requestUrl: cp_url(`forms/${this.form}/submissions`),
        };
    },

    computed: {
        parameters() {
            return this.$refs.listing?.parameters;
        },
    },

    methods: {
        refresh() {
            this.$refs.listing?.refresh();
        },

        view(event, submission) {
            if (this.viewInStack) event.preventDefault();
            this.$emit('view', submission);
        },
    },

    expose: ['parameters', 'refresh'],
};
</script>
