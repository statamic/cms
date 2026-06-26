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
        :allow-customizing-columns="view === 'entries'"
        :show-results="view === 'entries'"
        push-query
    >
        <template #toolbar-actions>
            <slot name="toolbar-actions" />
        </template>

        <template #results>
            <slot name="results" />
        </template>

        <template #cell-datestamp="{ row: submission, value, isColumnVisible }">
            <Link class="title-index-field" :href="submission.url" @click.stop>
                <SubmissionStatusIndicator v-if="!isColumnVisible('status')" :status="submission.status" />
                <span><date-time :of="value" /></span>
            </Link>
        </template>
        <template #cell-status="{ row: submission }">
            <SubmissionStatusIndicator :status="submission.status" show-label :show-dot="false" />
        </template>
        <template #prepended-row-actions="{ row: submission }">
            <DropdownItem :text="__('View')" :href="submission.url" icon="eye" />
        </template>
    </Listing>
</template>

<script>
import { Listing, DropdownItem, Badge } from '@/components/ui';
import { Link } from '@inertiajs/vue3';
import SubmissionStatusIndicator from '@/components/forms/SubmissionStatusIndicator.vue';

export default {
    components: { SubmissionStatusIndicator, Link, DropdownItem, Listing, Badge },

    props: {
        form: String,
        actionUrl: String,
        sortColumn: String,
        sortDirection: String,
        columns: Array,
        filters: Array,
        view: {
            type: String,
            default: 'entries',
        },
    },

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
    },

    expose: ['parameters', 'refresh'],
};
</script>
