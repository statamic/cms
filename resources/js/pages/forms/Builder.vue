<script setup>
import Layout from '@/pages/layout/Layout.vue';
import PanelLayout from '@/pages/layout/PanelLayout.vue';
import FormsLayout from './Layout.vue';
import { Button, Header, Icon, StatusIndicator, ToggleGroup, ToggleItem } from '@ui';
import LayoutPanel from '@/pages/layout/LayoutPanel.vue';
import { computed, nextTick, onMounted, onUnmounted, ref, watch } from 'vue';
import { Draggable, Sortable, Plugins } from '@shopify/draggable';
import FieldtypeSelector from '@/components/forms/Builder/FieldtypeSelector.vue';
import Section from '@/components/forms/Builder/Section.vue';

defineOptions({ layout: [Layout, PanelLayout, FormsLayout] });

const props = defineProps({
    form: Object,
    fieldtypes: Array,
});

// todo: the original value should come from a prop (initialFormFields)
const formFields = ref({
    sections: [
        {
            _id: 'abc',
            collapsed: false,
            title: 'Section 1',
            fields: [],
            values: {},
            meta: {},
        },
    ],
});

const editingField = ref(null);
const fieldView = ref('expanded');
const sectionRefs = ref({});
const sections = computed(() => formFields.value.sections);
const shouldShowViewSelector = computed(() => sections.value.some((s) => s.fields.length > 0));

let draggableInstance = null;
let currentDropTarget = null;
let lastClientY = 0;

const makeFieldsDraggable = () => {
    const containers = document.querySelectorAll('.fieldtype-source, .section-drop-zone');
    if (containers.length === 0) return;

    draggableInstance = new Draggable(containers, {
        draggable: '.fieldtype-draggable',
        mirror: {
            constrainDimensions: true,
            appendTo: 'body',
        },
    });

    draggableInstance.on('drag:move', (event) => lastClientY = event.sensorEvent.clientY);

    draggableInstance.on('drag:over:container', (event) => {
        if (event.overContainer.classList.contains('section-drop-zone')) {
            currentDropTarget = event.overContainer;
        }
    });

    draggableInstance.on('drag:out:container', (event) => {
        if (currentDropTarget === event.overContainer) {
            currentDropTarget = null;
        }
    });

    draggableInstance.on('drag:stop', (event) => {
        if (!currentDropTarget) return;

        const fieldtypeHandle = event.source.dataset.fieldtype;
        const sectionId = currentDropTarget.dataset.sectionDropZone;
        currentDropTarget = null;

        if (!fieldtypeHandle || !sectionId) return;

        const fieldElements = document.querySelectorAll(`[data-section-drop-zone="${sectionId}"] [data-field-item]`);

        let index = 0;

        for (const el of fieldElements) {
            const rect = el.getBoundingClientRect();
            if (lastClientY > rect.top + rect.height / 2) index++;
        }

        sectionRefs.value[sectionId]?.addField(fieldtypeHandle, index);
    });
};

let sortableInstance = null;

const makeFieldsSortable = () => {
    sortableInstance?.destroy();

    const containers = document.querySelectorAll('.field-sort-container');
    if (containers.length === 0) return;

    sortableInstance = new Sortable(containers, {
        draggable: '[data-field-item]',
        handle: '[data-field-item]',
        distance: 5,
        mirror: {
            constrainDimensions: true,
            appendTo: 'body',
        },
        swapAnimation: { vertical: true },
        plugins: [Plugins.SwapAnimation],
        exclude: {
            plugins: [Draggable.Plugins.Focusable],
        },
    });

    sortableInstance.on('drag:start', () => document.documentElement.classList.add('cursor-grabbing'));
    sortableInstance.on('drag:stop', () => document.documentElement.classList.remove('cursor-grabbing'));

    sortableInstance.on('sortable:stop', (event) => {
        const { oldIndex, newIndex, oldContainer, newContainer } = event;

        const originalSectionId = oldContainer.dataset.sortSection;
        const targetSectionId = newContainer.dataset.sortSection;

        if (!originalSectionId || !targetSectionId) return;
        if (originalSectionId === targetSectionId && oldIndex === newIndex) return;

        moveField(originalSectionId, targetSectionId, oldIndex, newIndex);
    });
};

const moveField = (originalSectionId, targetSectionId, oldIndex, newIndex) => {
    const originalSection = sections.value.find((s) => s._id === originalSectionId);
    const targetSection = sections.value.find((s) => s._id === targetSectionId);
    if (!originalSection || !targetSection) return;

    const [field] = originalSection.fields.splice(oldIndex, 1);

    if (originalSectionId !== targetSectionId) {
        targetSection.values[field.handle] = originalSection.values[field.handle];
        targetSection.meta[field.handle] = originalSection.meta[field.handle];
        delete originalSection.values[field.handle];
        delete originalSection.meta[field.handle];
    }

    targetSection.fields.splice(newIndex, 0, field);
};

watch(
    () => formFields.value.sections.map((s) => s.fields.length).join(','),
    () => nextTick(makeFieldsSortable),
);

const onEscape = (event) => {
    if (event.key === 'Escape' && editingField.value) {
        editingField.value = null;
    }
};

onMounted(() => {
    nextTick(() => {
        makeFieldsDraggable();
        makeFieldsSortable();
    });

    document.addEventListener('keydown', onEscape);
});

onUnmounted(() => {
    draggableInstance?.destroy();
    sortableInstance?.destroy();

    document.removeEventListener('keydown', onEscape);
});




// TODO: Refactor everything below this line
const formTitle = computed(() => props.form?.title || __('Untitled Form'));
const formPageTotal = 1;
const inspectorTarget = ref('field');
const age = ref(null);
const fanLength = ref('');
const favoriteThing = ref('');
const favoriteAlbum = ref(null);
const editingFieldWidth = ref(100);
const panelCollapsed = ref(false);
const nextPageButtonLabel = ref(__('Next Page'));
const totalFieldCount = computed(() => 4);
const albumOptions = [
    { label: __('Days of Thunder'), value: 'days_of_thunder' },
    { label: __('Endless Summer'), value: 'endless_summer' },
    { label: __('Nocturnal'), value: 'nocturnal' },
    { label: __('Kids'), value: 'kids' },
    { label: __('Monsters'), value: 'monsters' },
    { label: __('Heroes'), value: 'heroes' },
    { label: __('Red, White, and Bruised: The Midnight Live'), value: 'red_white_and_bruised' },
];
const optionRows = ref(albumOptions.map((option) => ({
    option_value: option.value,
    cells: [option.label],
    hidden: false,
})));
const visibleAlbumOptions = computed(() => optionRows.value
    .filter((option) => !option.hidden)
    .map((option, index) => ({
        label: option.cells?.[0] || '',
        value: option.option_value || `option_${index + 1}`,
    })));
const isPageInspector = computed(() => inspectorTarget.value === 'page_1' || inspectorTarget.value === 'page_2');
const isActionInspector = computed(() => inspectorTarget.value === 'action_next' || inspectorTarget.value === 'action_submit');

const inspectActionButton = (target) => {
    inspectorTarget.value = target;
};
</script>

<template>
    <Teleport to="#form-layout-actions">
        <Button variant="primary" :aria-label="__('Save')">
            <Icon name="save" class="sm:hidden" />
            <span class="hidden sm:inline">{{ __('Save') }}</span>
        </Button>
    </Teleport>

    <Button
        class="min-[1000px]:hidden sticky top-3 mt-3 z-(--z-index-above) sm:-translate-x-3 md:-translate-x-9 col-start-1 row-start-1"
        popovertarget="popover-left-panel"
        :text="__('Form Builder')"
        icon="bar-sidebar-left-panel-open"
    />

    <LayoutPanel side="left">
        <FieldtypeSelector :fieldtypes :field-view />
    </LayoutPanel>

    <div class="col-span-full row-start-1 max-[1000px]:pt-14">
        <Header class="mx-auto max-w-5xl">
            <template #title>
                <StatusIndicator status="published" />
                {{ formTitle }}
            </template>
            <template #actions>
                <ToggleGroup v-if="shouldShowViewSelector" v-model="fieldView" size="xs">
                    <ToggleItem
                        value="expanded"
                        icon="expand"
                        :aria-label="__('Expanded view')"
                        v-tooltip="__('Expanded view')"
                    />
                    <ToggleItem
                        value="collapsed"
                        icon="collapse"
                        :aria-label="__('Collapsed view')"
                        v-tooltip="__('Collapsed view')"
                    />
                </ToggleGroup>
            </template>
        </Header>

<!--        <div-->
<!--            id="form-page-1"-->
<!--            class="mx-auto max-w-5xl max-[600px]:px-5 px-5.75 sm:px-6.25 mb-4 -mt-2"-->
<!--            role="button"-->
<!--            tabindex="0"-->
<!--            :aria-label="__('Page :current of :total', { current: 1, total: formPageTotal })"-->
<!--            data-form-page-label-->
<!--            data-form-page="1"-->
<!--            @click="inspectorTarget = 'page_1'"-->
<!--            @keydown.enter.prevent="inspectorTarget = 'page_1'"-->
<!--            @keydown.space.prevent="inspectorTarget = 'page_1'"-->
<!--        >-->
<!--            <div class="flex items-center gap-4 cursor-pointer">-->
<!--                <div class="flex items-center gap-2 flex-1">-->
<!--                    <div class="h-px min-w-0 flex-1 bg-gray-200 dark:bg-gray-700" aria-hidden="true" />-->
<!--                    <span v-tooltip="__('Logic attached')">-->
<!--                        <Icon data-logic-attached name="logic-tree" class="size-3.5! shrink-0 text-gray-400 dark:text-gray-600" aria-hidden="true" />-->
<!--                    </span>-->
<!--                </div>-->
<!--                <div-->
<!--                    class="flex shrink-0 items-center gap-2 rounded-xl border border-dashed border-gray-300 px-3.5 py-2 text-sm font-medium text-gray-700 dark:border-gray-700 dark:text-gray-200 scroll-mt-[7rem]"-->
<!--                    :data-editing-item="inspectorTarget === 'page_1' ? '' : undefined"-->
<!--                    :class="inspectorTarget === 'page_1' ? 'bg-blue-50 border-blue-400! dark:bg-blue-950 dark:border-blue-700!' : ''"-->
<!--                >-->
<!--                    <Icon name="page" class="size-4 shrink-0 text-gray-500 dark:text-gray-400" aria-hidden="true" />-->
<!--                    {{ __('Page :current of :total', { current: 1, total: formPageTotal }) }}-->
<!--                </div>-->
<!--                <div class="h-px min-w-0 flex-1 bg-gray-200 dark:bg-gray-700" aria-hidden="true" />-->
<!--            </div>-->
<!--        </div>-->

        <Section
            v-for="section in sections"
            :key="section._id"
            :ref="(el) => sectionRefs[section._id] = el"
            :section
            :fieldtypes
            :field-view
            v-model:editing-field="editingField"
        />

<!--        <Panel-->
<!--            class="mx-auto max-w-5xl"-->
<!--            :class="{ 'pb-0': panelCollapsed }"-->
<!--            :data-panel-collapsed="panelCollapsed ? 'true' : 'false'"-->
<!--        >-->
<!--            <PanelHeader class="relative flex items-center justify-between">-->
<!--                <Heading :text="__('Main Section')" />-->
<!--                <Button-->
<!--                    @click="panelCollapsed = !panelCollapsed"-->
<!--                    class="static! [&_svg]:size-3.5 rounded-xl after:content-[''] after:absolute after:inset-0"-->
<!--                    :icon="panelCollapsed ? 'expand' : 'collapse'"-->
<!--                    size="sm"-->
<!--                    variant="ghost"-->
<!--                    :aria-label="__('Toggle section visibility')"-->
<!--                />-->
<!--            </PanelHeader>-->

<!--            <div-->
<!--                style="&#45;&#45;tw-ease: ease;"-->
<!--                class="h-auto visible transition-[height,visibility] duration-[250ms,2s]"-->
<!--                :class="{ 'h-0! invisible! overflow-clip': panelCollapsed }"-->
<!--            >-->
<!--                <Card>-->
<!--                    <div class="space-y-7" :data-fields-collapsed="fieldView === 'collapsed' ? 'true' : null">-->
<!--                        <div data-fieldset-group class="space-y-7">-->
<!--                            <div id="fieldset-start">-->
<!--                                <span data-fieldset-label class="inline-flex gap-1.75 rounded-md font-mono text-2xs text-indigo-800">-->
<!--                                    <span class="inline-flex" v-tooltip="__('Fieldset')">-->
<!--                                        <Icon name="link" class="size-3.5" aria-hidden="true" />-->
<!--                                    </span>-->
<!--                                    <span class="sr-only">{{ __('Fieldset') }}</span>-->
<!--                                </span>-->
<!--                                <Field :label="__('What do you like most about our band?')">-->
<!--                                    <template #label>-->
<!--                                        <Label for="favorite-thing-field">-->
<!--                                            <span class="inline-flex flex-wrap items-center gap-x-2 gap-y-1">-->
<!--                                                <Icon name="text-long" data-collapsed-field-icon class="size-3.5 me-1 text-purple-500 dark:text-purple-400" aria-hidden="true" />-->
<!--                                                {{ __('What do you like most about our band?') }}-->
<!--                                                <span class="relative -top-px -ms-0.5 text-red-600" :aria-label="__('Required')">*</span>-->
<!--                                            </span>-->
<!--                                        </Label>-->
<!--                                    </template>-->
<!--                                    <span class="absolute z-(&#45;&#45;z-index-above) top-1 max-sm:-right-2 sm:-left-14" v-tooltip="__('Logic attached')">-->
<!--                                        <Icon data-logic-attached name="logic-tree" class="size-3.5! text-gray-400 dark:text-gray-600" aria-hidden="true" />-->
<!--                                    </span>-->
<!--                                    <Textarea id="favorite-thing-field" v-model="favoriteThing" :rows="4" resize="vertical" required />-->
<!--                                </Field>-->
<!--                            </div>-->

<!--                            <div id="fieldset-end">-->
<!--                                <Field :label="__('How long have you been a fan?')" :instructions="__('If you don\'t remember, just give your best estimate')">-->
<!--                                    <template #label>-->
<!--                                        <Label for="fan-length-field">-->
<!--                                            <span class="inline-flex flex-wrap items-center gap-x-2 gap-y-1">-->
<!--                                                <Icon name="text-short" data-collapsed-field-icon class="size-3.5 me-1 text-purple-500 dark:text-purple-400" aria-hidden="true" />-->
<!--                                                {{ __('How long have you been a fan?') }}-->
<!--                                            </span>-->
<!--                                        </Label>-->
<!--                                    </template>-->
<!--                                    <span class="absolute z-(&#45;&#45;z-index-above) top-1 max-sm:-right-2 sm:-left-14" v-tooltip="__('Logic attached')">-->
<!--                                        <Icon data-logic-attached name="logic-tree" class="size-3.5! text-gray-400 dark:text-gray-600" aria-hidden="true" />-->
<!--                                    </span>-->
<!--                                    <Input id="fan-length-field" v-model="fanLength" />-->
<!--                                </Field>-->
<!--                            </div>-->
<!--                        </div>-->

<!--                        <div-->
<!--                            id="editing-field"-->
<!--                            :data-editing-field="isPageInspector || isActionInspector ? undefined : ''"-->
<!--                            :data-editing-item="isPageInspector || isActionInspector ? undefined : ''"-->
<!--                            @click="inspectorTarget = 'field'"-->
<!--                        >-->
<!--                            <div-->
<!--                                v-if="!isPageInspector && !isActionInspector"-->
<!--                                class="!absolute z-(&#45;&#45;z-index-above) -top-0.5 end-0.5 flex items-center"-->
<!--                            >-->
<!--                                <WidthSelector-->
<!--                                    v-model="editingFieldWidth"-->
<!--                                    size="base"-->
<!--                                    variant="filled"-->
<!--                                    class="me-2 bg-blue-50! border-blue-300! dark:bg-blue-950/40! dark:border-blue-600! [&_[data-state]]:!border-blue-200 dark:[&_[data-state]]:!border-blue-700 [&_[data-state='selected']]:bg-blue-100! [&_[data-state='selected'][data-last='false']]:!border-blue-100 [&_[data-last='true']]:!border-blue-300 dark:[&_[data-state='selected']]:bg-blue-900! dark:[&_[data-state='selected'][data-last='false']]:!border-blue-900 dark:[&_[data-last='true']]:!border-blue-600"-->
<!--                                />-->
<!--                                <Button-->
<!--                                    size="sm"-->
<!--                                    inset-->
<!--                                    icon="duplicate"-->
<!--                                    variant="subtle"-->
<!--                                    :aria-label="__('Duplicate field')"-->
<!--                                    :title="__('Duplicate field')"-->
<!--                                    class="[&_svg]:opacity-45"-->
<!--                                />-->
<!--                                <Button-->
<!--                                    size="sm"-->
<!--                                    inset-->
<!--                                    icon="eye"-->
<!--                                    variant="subtle"-->
<!--                                    :aria-label="__('Hide field')"-->
<!--                                    :title="__('Hide field')"-->
<!--                                    class="[&_svg]:opacity-45"-->
<!--                                />-->
<!--                                <Button-->
<!--                                    size="sm"-->
<!--                                    inset-->
<!--                                    icon="trash"-->
<!--                                    variant="subtle"-->
<!--                                    :aria-label="__('Remove field')"-->
<!--                                    :title="__('Remove field')"-->
<!--                                    class="[&_svg]:opacity-45"-->
<!--                                />-->
<!--                            </div>-->
<!--                            <Field :label="__('Which album was your favorite?')">-->
<!--                                <template #label>-->
<!--                                    <Label>-->
<!--                                        <span class="inline-flex flex-wrap items-center gap-x-2 gap-y-1">-->
<!--                                            <Icon name="fieldtype-radio" data-collapsed-field-icon class="size-3.5 me-1 text-orange-600 dark:text-orange-400" aria-hidden="true" />-->
<!--                                            {{ __('Which album was your favorite?') }}-->
<!--                                        </span>-->
<!--                                    </Label>-->
<!--                                </template>-->
<!--                                <RadioGroup v-model="favoriteAlbum">-->
<!--                                    <Radio-->
<!--                                        v-for="album in visibleAlbumOptions"-->
<!--                                        :key="album.value"-->
<!--                                        :value="album.value"-->
<!--                                        :label="album.label"-->
<!--                                    />-->
<!--                                </RadioGroup>-->
<!--                            </Field>-->
<!--                        </div>-->

<!--                        <Field id="age-field" class="opacity-60" :label="__('How old are you?')">-->
<!--                            <template #label>-->
<!--                                <Label for="age-field">-->
<!--                                    <span class="inline-flex flex-wrap items-center gap-x-2 gap-y-1">-->
<!--                                        <Icon name="number" data-collapsed-field-icon class="size-3.5 me-1 text-teal-600 dark:text-teal-400" aria-hidden="true" />-->
<!--                                        {{ __('How old are you?') }}-->
<!--                                        <Icon name="eye-closed" class="size-3.5! text-gray-400 dark:text-gray-500" :aria-label="__('Hidden')" v-tooltip="__('Hidden')" />-->
<!--                                    </span>-->
<!--                                </Label>-->
<!--                            </template>-->
<!--                            <Input id="age-field" v-model="age" type="number" />-->
<!--                        </Field>-->

<!--                        <div-->
<!--                            id="action-next-button"-->
<!--                            class="mt-8"-->
<!--                        >-->
<!--                            <Button-->
<!--                                variant="primary"-->
<!--                                @click.prevent="inspectActionButton('action_next')"-->
<!--                                :data-editing-field="inspectorTarget === 'action_next' ? '' : undefined"-->
<!--                                :data-editing-item="inspectorTarget === 'action_next' ? '' : undefined"-->
<!--                                class="border-0! dark:border-0! ring-0! shadow-none!"-->
<!--                                style="&#45;&#45;theme-color-primary: var(&#45;&#45;theme-color-gray-950)"-->
<!--                                :text="nextPageButtonLabel"-->
<!--                            />-->
<!--                        </div>-->
<!--                    </div>-->
<!--                </Card>-->
<!--            </div>-->
<!--        </Panel>-->

        <p
            v-if="fieldView === 'collapsed'"
            class="mx-auto text-center max-w-5xl max-[600px]:p-5 px-5.75 sm:px-6.25 mb-5 text-sm text-gray-600 dark:text-gray-300"
        >
            <strong>{{ totalFieldCount }}</strong> {{ __n('field on this form|fields on this form', totalFieldCount) }}
        </p>
    </div>

    <Button
        class="min-[1000px]:hidden sticky top-3 mt-3 z-(--z-index-above) sm:translate-x-3 md:translate-x-9 mb-5 col-start-3 row-start-1"
        popovertarget="popover-right-panel"
        :text="__('Settings')"
        icon="cog"
    />

    <LayoutPanel side="right">
        <!-- TODO: Wire up field/page/action settings -->
        <p v-if="editingField">{{ editingField.config.display }}</p>
    </LayoutPanel>
</template>
