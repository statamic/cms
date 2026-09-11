<script setup>
import { ref, computed, onMounted, getCurrentInstance } from 'vue';
import Head from '@/pages/layout/Head.vue';
import DynamicHtmlRenderer from '@/components/DynamicHtmlRenderer.vue';
import { Icon, Button, EmptyStateMenu, EmptyStateItem, DocsCallout } from '@ui';
import { SortableList } from '@/components/sortable/Sortable.js';
import WidgetEditChrome from '@/components/dashboard/WidgetEditChrome.vue';
import WidgetPicker from '@/components/dashboard/WidgetPicker.vue';
import WidgetConfigStack from '@/components/dashboard/WidgetConfigStack.vue';
import useArchitecturalBackground from '@/pages/layout/architectural-background.js';
import { router } from '@inertiajs/vue3';
import { clone } from '@/bootstrap/globals.js';

const props = defineProps({
    widgets: Array,
    widgetConfigs: { type: Array, default: () => [] },
    canEditWidgets: { type: Boolean, default: false },
    widgetMetaUrl: String,
    widgetUpdateUrl: String,
    pro: Boolean,
    blueprintsUrl: String,
    collectionsCreateUrl: String,
    navigationCreateUrl: String,
});

const { $axios, $toast } = getCurrentInstance().appContext.config.globalProperties;

const editing = ref(false);
const draftItems = ref([]);
const availableWidgets = ref(null);
const loadingMeta = ref(false);
const configuringIndex = ref(null);
const saving = ref(false);
const isDragging = ref(false);

const sortableOptions = {
    classes: {
        mirror: 'dashboard-widget-mirror',
    },
    swapAnimation: {
        duration: 220,
        easingFunction: 'cubic-bezier(0.22, 1, 0.36, 1)',
        horizontal: true,
        vertical: true,
    },
    mirror: {
        constrainDimensions: true,
        appendTo: 'body',
    },
};

const widgetsMetaByHandle = computed(() => {
    if (!availableWidgets.value) return {};
    return Object.fromEntries(availableWidgets.value.map((w) => [w.handle, w]));
});

const configuringWidget = computed(() => {
    if (configuringIndex.value === null) return null;
    return draftItems.value[configuringIndex.value]?.config || null;
});

const configuringMeta = computed(() => {
    return configuringWidget.value ? widgetsMetaByHandle.value[configuringWidget.value.type] : null;
});

const unifiedWidgets = computed(() => {
    if (editing.value) {
        return draftItems.value.map((item) => ({ id: item.id, config: item.config, display: item.display }));
    }
    return props.widgets.map((widget, i) => ({ id: `widget-${i}`, config: null, display: widget }));
});

onMounted(() => {
    if (!props.widgets.length && !editing.value) useArchitecturalBackground();
});

function classes(source) {
    return `${source?.classes ?? ''} widget-w-${source?.width}`;
}

function ensureMetaLoaded() {
    if (availableWidgets.value || loadingMeta.value) return;
    loadingMeta.value = true;
    $axios.get(props.widgetMetaUrl)
        .then((response) => { availableWidgets.value = response.data; })
        .catch(() => $toast.error(__('Could not load widgets.')))
        .finally(() => { loadingMeta.value = false; });
}

function startEditing() {
    draftItems.value = props.widgetConfigs.map((config, i) => ({
        id: `widget-${i}`,
        config: clone(config),
        display: props.widgets[i] || null,
    }));
    editing.value = true;
    ensureMetaLoaded();
}

function cancelEditing() {
    editing.value = false;
    draftItems.value = [];
}

function widgetPicked(widget) {
    const newConfig = { type: widget.handle, ...(widget.defaults || {}) };
    draftItems.value.push({
        id: `${widget.handle}-${Date.now()}`,
        config: newConfig,
        display: null,
    });
    configuringIndex.value = draftItems.value.length - 1;
}

function configureWidget(index) {
    configuringIndex.value = index;
}

function widgetConfigSaved(updated) {
    if (configuringIndex.value === null) return;
    const existing = draftItems.value[configuringIndex.value];
    draftItems.value.splice(configuringIndex.value, 1, { ...existing, config: updated });
    configuringIndex.value = null;
}

function removeWidget(index) {
    if (configuringIndex.value === index) {
        configuringIndex.value = null;
    } else if (configuringIndex.value !== null && configuringIndex.value > index) {
        configuringIndex.value--;
    }

    draftItems.value.splice(index, 1);
}

function updateWidth(index, width) {
    const item = draftItems.value[index];
    draftItems.value.splice(index, 1, { ...item, config: { ...item.config, width } });
}

function onSort(sortedItems) {
    if (!editing.value) return;

    draftItems.value = sortedItems.map((item) => ({
        id: item.id ?? `${item.config?.type}-${Date.now()}`,
        config: item.config,
        display: item.display,
    }));
}

function save() {
    saving.value = true;
    $axios.patch(props.widgetUpdateUrl, { widgets: draftItems.value.map((i) => i.config) })
        .then(() => {
            editing.value = false;
            router.reload();
        })
        .catch(() => $toast.error(__('Something went wrong')))
        .finally(() => { saving.value = false; });
}
</script>

<template>
    <Head :title="__('Dashboard')" />

    <template v-if="editing || widgets.length">
        <ui-header :title="__('Dashboard')" icon="dashboard">
            <template v-if="editing">
                <WidgetPicker :widgets="availableWidgets || []" @picked="widgetPicked" />
                <Button :text="__('Cancel')" @click="cancelEditing" />
                <Button :text="__('Save')" variant="primary" :disabled="saving" @click="save" />
            </template>
            <Button v-else-if="canEditWidgets" :text="__('Configure')" icon="edit" @click="startEditing" />
        </ui-header>

        <SortableList
            :model-value="unifiedWidgets"
            item-class="dashboard-widget-sortable"
            handle-class="dashboard-widget-handle"
            :disabled="!editing"
            :animate="true"
            :constrain-dimensions="true"
            :distance="8"
            :options="sortableOptions"
            @dragstart="isDragging = true"
            @dragend="isDragging = false"
            @update:model-value="onSort"
        >
            <div
                class="widgets @container/widgets"
                :class="{ 'dashboard-editing': editing, 'dashboard-is-dragging': isDragging }"
            >
                <div
                    v-for="(item, index) in unifiedWidgets"
                    :key="item.id"
                    class="dashboard-widget-sortable"
                    :class="[classes(item.config ?? item.display), { 'starting-style-transition': !editing && !isDragging }]"
                >
                    <div class="dashboard-widget-inner">
                        <WidgetEditChrome
                            v-if="editing && item.config"
                            :config="item.config"
                            :meta="widgetsMetaByHandle[item.config.type]"
                            @configure="configureWidget(index)"
                            @remove="removeWidget(index)"
                            @update:width="updateWidth(index, $event)"
                        />
                        <component v-if="item.display?.component" :is="item.display.component.name" v-bind="item.display.component.props" />
                        <DynamicHtmlRenderer v-else-if="item.display?.html" :html="item.display.html" />
                        <div v-else-if="editing" class="dashboard-widget-placeholder">
                            <Icon :name="widgetsMetaByHandle[item.config?.type]?.icon ?? 'code-block'" class="size-8 text-gray-400" />
                            <div>
                                <p class="text-sm font-medium text-gray-700 dark:text-gray-200">
                                    {{ widgetsMetaByHandle[item.config?.type]?.title ?? item.config?.type }}
                                </p>
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __('Configure this widget to preview it here.') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div v-if="editing && !draftItems.length" class="dashboard-widget-empty">
                    <Icon name="dashboard" class="size-10 text-gray-400" />
                    <div>
                        <p class="text-sm font-medium text-gray-700 dark:text-gray-200">{{ __('No widgets yet') }}</p>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Add a widget to start customizing your dashboard.') }}</p>
                    </div>
                    <WidgetPicker :widgets="availableWidgets || []" @picked="widgetPicked" />
                </div>
            </div>
        </SortableList>

        <WidgetConfigStack
            v-if="configuringWidget"
            :config="configuringWidget"
            :meta="configuringMeta"
            @closed="configuringIndex = null"
            @saved="widgetConfigSaved"
        />
    </template>

    <template v-else>
        <header class="py-8 pt-16 text-center">
            <h1 class="text-[25px] font-medium antialiased flex justify-center items-center gap-2 sm:gap-3">
                <Icon name="dashboard" class="size-5 text-gray-500" />
                {{ __('Dashboard') }}
            </h1>
            <div v-if="canEditWidgets" class="mt-4">
                <Button :text="__('Edit Dashboard')" icon="edit" @click="startEditing" />
            </div>
        </header>

        <EmptyStateMenu
            :heading="__('statamic::messages.getting_started_widget_header')"
            :subheading="__('statamic::messages.getting_started_widget_intro')"
        >
            <EmptyStateItem
                href="https://statamic.dev"
                icon="docs"
                :heading="__('Read the Documentation')"
                :description="__('statamic::messages.getting_started_widget_docs')"
            />
            <EmptyStateItem
                v-if="!pro"
                href="https://statamic.dev/licensing"
                icon="pro-ribbon"
                :heading="__('Enable Pro Mode')"
                :description="__('statamic::messages.getting_started_widget_pro')"
            />
            <EmptyStateItem
                :href="blueprintsUrl"
                icon="blueprints"
                :heading="__('Create a Blueprint')"
                :description="__('statamic::messages.blueprints_intro')"
            />
            <EmptyStateItem
                :href="collectionsCreateUrl"
                icon="collections"
                :heading="__('Create a Collection')"
                :description="__('statamic::messages.getting_started_widget_collections')"
            />
            <EmptyStateItem
                :href="navigationCreateUrl"
                icon="navigation"
                :heading="__('Create a Navigation')"
                :description="__('statamic::messages.getting_started_widget_navigation')"
            />
        </EmptyStateMenu>
    </template>

    <DocsCallout :topic="__('Widgets')" url="widgets" />
</template>
