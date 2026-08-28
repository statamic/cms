<script setup lang="ts">
import { Listing, DropdownItem, Panel, PanelFooter, ToggleGroup, ToggleItem } from '@/components/ui';
import { Link } from '@inertiajs/vue3';
import SubmissionStatusIndicator from '@/components/forms/SubmissionStatusIndicator.vue';
import Table from '@ui/Listing/Table.vue';
import Search from '@ui/Listing/Search.vue';
import Filters from '@ui/Listing/Filters.vue';
import CustomizeColumns from '@ui/Listing/CustomizeColumns.vue';
import Pagination from '@ui/Listing/Pagination.vue';
import Presets from '@ui/Listing/Presets.vue';
import { computed, useTemplateRef, ref, watch } from 'vue';
import { preferences } from '@api';
import Summary from '@/components/forms/summary/Summary.vue';

enum View {
    Submissions = 'submissions',
    Summary = 'summary',
}

const props = defineProps<{
    form: string;
    actionUrl: string;
    summaryUrl?: string;
    chartsUpdateUrl?: string;
    sortColumn: string;
    sortDirection: string;
    columns: any[];
    filters: any[];
    can: Record<string, boolean>;
}>();

const listing = useTemplateRef('listing');
const summary = useTemplateRef('summary');
const preferencesPrefix = `forms.${props.form}`;
const requestUrl = cp_url(`forms/${props.form}/submissions`);
const view = ref<View>(props.summaryUrl ? preferences.get(`${preferencesPrefix}.view`, View.Submissions) : View.Submissions);

const parameters = computed(() => listing.value?.parameters);

const refresh = (): void => {
    view.value === View.Submissions
        ? listing.value?.refresh()
        : summary.value?.refresh();
};

watch(view, (view: View): void => preferences.set(`${preferencesPrefix}.view`, view));

defineExpose({ parameters, refresh });
</script>

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
        push-query
    >
        <template #default="{ items, showPresets, allowSearch, allowCustomizingColumns, hasFilters, meta }">
            <Presets v-if="showPresets" />
            <div v-if="allowSearch || hasFilters || allowCustomizingColumns" class="relative overflow-clip flex items-center gap-2 sm:gap-3 min-h-16 starting-style-transition st-overflow-clip-margin">
                <div class="flex flex-1 items-center gap-2 sm:gap-3 overflow-x-auto -ms-1 ps-1 py-1">
                    <Search v-if="allowSearch" />
                    <Filters v-if="hasFilters" />
                </div>

                <ToggleGroup v-if="summaryUrl" v-model="view">
                    <ToggleItem
                        :value="View.Submissions"
                        icon="layout-list"
                        :aria-label="__('Submissions')"
                        v-tooltip="__('Submissions')"
                    />
                    <ToggleItem
                        :value="View.Summary"
                        icon="chart-increase"
                        :aria-label="__('Summary')"
                        v-tooltip="__('Summary')"
                    />
                </ToggleGroup>

                <CustomizeColumns v-if="allowCustomizingColumns && view === View.Submissions" />
            </div>

            <template v-if="view === View.Submissions">
                <div
                    v-if="!items.length"
                    class="rounded-lg border border-dashed border-gray-300 dark:border-gray-700 p-6 text-center text-gray-500"
                    v-text="__('No results')"
                />

                <Panel v-else class="relative overflow-x-auto" style="container-type: scroll-state;">
                    <Table>
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
                    </Table>
                    <PanelFooter v-if="meta">
                        <Pagination />
                    </PanelFooter>
                </Panel>
            </template>

            <template v-if="view === View.Summary">
                <Summary
                    ref="summary"
                    :form
                    :summary-url
                    :charts-update-url
                    :can-edit="can.edit"
                />
            </template>
        </template>
    </Listing>
</template>
