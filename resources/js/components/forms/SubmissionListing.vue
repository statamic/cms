<template>
    <Listing
        :url="requestUrl"
        :columns="columns"
        :action-url="actionUrl"
        :action-context="{ form }"
        :sort-column="sortColumn"
        :sort-direction="sortDirection"
        :preferences-prefix="preferencesPrefix"
        :filters="filters"
    >
        <template #cell-datestamp="{ row: submission, value }">
            <a :href="submission.url">
                <date-time :of="value" />
            </a>
        </template>
        <template #prepended-row-actions="{ row: submission }">
            <DropdownItem :text="__('View')" :href="submission.url" icon="eye" />
        </template>
    </Listing>
</template>

<script>
import { Listing, DropdownItem } from '@/components/ui';

export default {
    components: { DropdownItem, Listing },

    props: {
        form: String,
        actionUrl: String,
        sortColumn: String,
        sortDirection: String,
        columns: Array,
        filters: Array,
    },

    data() {
        return {
            preferencesPrefix: `forms.${this.form}`,
            requestUrl: cp_url(`forms/${this.form}/submissions`),
        };
    },
};
</script>
