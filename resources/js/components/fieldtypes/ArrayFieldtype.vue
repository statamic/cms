<template>
    <div :class="{ 'w-full min-w-0': isCompact }">
        <component :is="wrapperComponent" v-bind="wrapperBinds">
            <template v-if="isCompact" #trigger>
                <ui-button
                    class="w-full min-w-0 shrink justify-between"
                    icon-append="chevron-down"
                    :aria-expanded="compactOpen"
                    aria-haspopup="dialog"
                >
                    <span
                        class="block min-w-0 flex-1 truncate text-start font-normal"
                        :class="{ 'text-gray-500 dark:text-gray-400': !hasCompactValues }"
                    >
                        {{ compactTriggerText }}
                    </span>
                </ui-button>
            </template>

            <div ref="editor" @keydown.enter="addRowOnEnter">
                <ui-input-group v-if="isSingle">
                    <ui-input-group-prepend>
                        <select
                            class="appearance-none border-0 bg-transparent text-sm shadow-none outline-hidden"
                            @input="setKey($event.target.value)"
                        >
                            <option
                                v-for="(element, index) in keyedData"
                                v-text="keys[element.key] || element.key"
                                :key="element._id"
                                :value="element.key"
                                :selected="element.key === selectedKey"
                            />
                        </select>
                        <ui-icon name="chevron-down" class="size-3 ms-1" />
                    </ui-input-group-prepend>
                    <template v-for="(element, index) in keyedData">
                        <ui-input
                            v-if="element.key === selectedKey"
                            v-model="data[index].value"
                            input-class="border-l-0"
                            :key="element._id"
                            :id="fieldId + '__' + element.key"
                            :readonly="isReadOnly"
                            :input-attrs="{ dir: contentDirection }"
                        />
                    </template>
                </ui-input-group>

                <table class="table-contained" :class="{ 'mb-0': isCompact }" v-else-if="isKeyed">
                    <tbody>
                        <tr v-if="data" v-for="(element, index) in keyedData" :key="element._id">
                            <th class="w-1/4">
                                <label :for="fieldId + '__' + element.key">{{ keys[element.key] || element.key }}</label>
                            </th>
                            <td>
                                <input
                                    type="text"
                                    class="w-full input-text"
                                    :id="fieldId + '__' + element.key"
                                    v-model="data[index].value"
                                    :readonly="isReadOnly"
                                    :dir="contentDirection"
                                />
                            </td>
                        </tr>
                    </tbody>
                </table>

                <table class="table-contained" :class="{ 'mb-0': isCompact }" v-if="isDynamic && valueCount">
                    <thead>
                        <tr>
                            <th class="grid-drag-handle-header" v-if="!isReadOnly"></th>
                            <th class="w-1/4">{{ keyHeader }}</th>
                            <th :class="{ 'border-e-0!': canToggleVisibility }">{{ valueHeader }}</th>
                            <th class="visibility-controls" v-if="canToggleVisibility"></th>
                            <th class="row-controls" v-if="!isReadOnly"></th>
                        </tr>
                    </thead>

                    <sortable-list
                        v-model="data"
                        :vertical="true"
                        item-class="sortable-row"
                        handle-class="sortable-handle"
                        :mirror="false"
                    >
                        <tbody>
                            <tr class="sortable-row" v-for="(element, index) in data" :key="element._id">
                                <td class="sortable-handle table-drag-handle" v-if="!isReadOnly"></td>
                                <td>
                                    <ui-input
                                        v-model="element.key"
                                        :readonly="isReadOnly"
                                        :input-attrs="{ dir: contentDirection }"
                                    />
                                </td>
                                <td>
                                    <ui-input
                                        v-model="element.value"
                                        :readonly="isReadOnly"
                                        :input-attrs="{ dir: contentDirection }"
                                    />
                                </td>
                                <td v-if="canToggleVisibility" class="visibility-controls">
                                    <ui-button
                                        :icon="element.hidden ? 'eye-closed' : 'eye'"
                                        variant="subtle"
                                        size="xs"
                                        round
                                        @click="toggleHidden(index)"
                                        :aria-label="element.hidden ? __('Show Option') : __('Hide Option')"
                                        v-tooltip="element.hidden ? __('Show Option') : __('Hide Option')"
                                    />
                                </td>
                                <td class="row-controls" v-if="!isReadOnly">
                                    <ui-button icon="x" variant="subtle" size="xs" round delete-action @click="deleteOrConfirm(index)" :aria-label="__('Delete Row')" v-tooltip="__('Delete Row')" />
                                </td>
                            </tr>
                        </tbody>
                    </sortable-list>
                </table>

                <div
                    v-if="(!isReadOnly && !isSingle && !isKeyed) || isCompact"
                    class="flex w-full items-center gap-2"
                    :class="{ 'mt-2': isCompact && valueCount }"
                >
                    <ui-button @click="addValue()" :disabled="atMax" v-if="!isReadOnly && !isSingle && !isKeyed" :text="addButton" size="sm" :class="compactFooterButtonClass" />
                    <ui-button v-if="isCompact" class="ms-auto" :class="compactFooterButtonClass" size="sm" @click="setCompactOpen(false)">
                        <span class="st-text-trim-cap">{{ __('Close') }}</span>
                        <span class="ms-1.5 inline-flex h-4 min-w-4 items-center justify-center rounded bg-gray-200/50 px-1 font-semibold uppercase text-[0.625rem] text-gray-600 dark:bg-gray-900 dark:text-gray-400/85">
                            Esc
                        </span>
                    </ui-button>
                </div>
            </div>
        </component>

        <confirmation-modal
            :open="deleting !== false"
            :title="__('Delete Row')"
            :bodyText="__('Are you sure you want to delete this row?')"
            :buttonText="__('Delete')"
            :danger="true"
            @confirm="deleteValue(deleting)"
            @cancel="deleteCancelled"
        />
    </div>
</template>

<script>
import Fieldtype from './Fieldtype.vue';
import { SortableList, SortableHelpers } from '../sortable/Sortable';
import { Button, Popover } from '@/components/ui';
import { useContentDirection } from '@/composables/content-direction';

export default {
    mixins: [Fieldtype, SortableHelpers],

    components: {
        SortableList,
        Button,
        Popover,
    },

    setup() {
        const { direction: contentDirection } = useContentDirection();

        return { contentDirection };
    },

    data() {
        const keys = Object.keys(this.value || {});
        const selectedKey = keys.length > 0 ? keys[0] : null;

        return {
            data: this.valueToData(this.value || []),
            selectedKey,
            deleting: false,
            compactOpen: false,
        };
    },

    watch: {
        data: {
            deep: true,
            handler(data) {
                this.update(this.dataToValue(data));
            },
        },

        value(value) {
            if (JSON.stringify(value) == JSON.stringify(this.dataToValue(this.data))) return;
            this.data = this.valueToData(value);
        },
    },

    computed: {
        keys() {
            return this.meta.keys || this.config.keys;
        },

        isKeyed() {
            return Boolean(Object.keys(this.keys).length);
        },

        isDynamic() {
            return !this.isKeyed;
        },

        isSingle() {
            return this.config.mode === 'single';
        },

        isCompact() {
            return this.config.compact === true;
        },

        wrapperComponent() {
            return this.isCompact ? Popover : 'div';
        },

        wrapperBinds() {
            if (!this.isCompact) return {};

            return {
                align: 'end',
                side: 'bottom',
                class: 'w-[32rem]',
                dismissible: this.deleting === false,
                excludeZManipulation: true,
                open: this.compactOpen,
                'onUpdate:open': this.setCompactOpen,
            };
        },

        keyedData() {
            return this.data.filter((element) => this.keys.hasOwnProperty(element.key));
        },

        maxItems() {
            return this.config.max_items || null;
        },

        valueCount() {
            return this.data.length;
        },

        atMax() {
            return this.maxItems ? this.valueCount >= this.maxItems : false;
        },

        addButton() {
            return __(this.config.add_button || 'Add Row');
        },

        keyHeader() {
            return __(this.config.key_header || 'Key');
        },

        valueHeader() {
            return __(this.config.value_header || 'Value');
        },

        canToggleVisibility() {
            return !this.isReadOnly && this.config.show_hide_toggle === true;
        },

        compactFooterButtonClass() {
            if (!this.isCompact) return;

            return 'from-white to-white hover:from-white hover:to-gray-50';
        },

        hasCompactValues() {
            return this.data.some((element) => element.key || element.value);
        },

        compactTriggerText() {
            if (!this.hasCompactValues) return this.addButton;

            return this.data
                .filter((element) => element.key || element.value)
                .map((element) => element.key || this.valueHeader)
                .join(', ');
        },

        replicatorPreview() {
            if (!this.showFieldPreviews) return;
            if (!this.value) return '';

            return Object.entries(this.value)
                .map(([key, value]) => `${key}: ${value}`)
                .filter(Boolean)
                .join(', ');
        },
    },

    methods: {
        valueToData(value) {
            return Object.entries(clone(value)).map(([key, val]) => {
                const item = this.newSortableValue(null, key);

                if (val !== null && typeof val === 'object' && 'value' in val) {
                    item.value = val.value;
                    item.hidden = val.hidden ?? false;
                } else {
                    item.value = val;
                }

                return item;
            });
        },

        dataToValue(data) {
            let obj = {};

            data.forEach((item) => {
                if (this.config.show_hide_toggle && item.hidden === true) {
                    obj[item.key] = { value: item.value, hidden: true };
                } else {
                    obj[item.key] = item.value;
                }
            });

            return obj;
        },

        setCompactOpen(open) {
            if (!open) {
                this.data = this.data.filter((element) => element.key || element.value);
            }

            this.compactOpen = open;

            if (open && !this.valueCount && !this.isReadOnly) {
                this.addValue();
            }
        },

        addRowOnEnter(event) {
            if (!this.isDynamic || this.isReadOnly || this.atMax) return;
            if (event.target.tagName !== 'INPUT') return;

            event.preventDefault();

            const rows = [...(this.$refs.editor?.querySelectorAll('tr.sortable-row') ?? [])];
            const current = event.target.closest('tr.sortable-row');
            const index = rows.indexOf(current);

            this.addValue(index === -1 ? this.data.length : index + 1);
        },

        addValue(index = this.data.length) {
            if (typeof index !== 'number' || Number.isNaN(index)) {
                index = this.data.length;
            }

            this.data.splice(index, 0, this.newSortableValue());
            this.$nextTick(() => {
                this.$refs.editor
                    ?.querySelectorAll('tr.sortable-row')[index]
                    ?.querySelector('input')
                    ?.focus();
            });
        },

        confirmDeleteValue(index) {
            this.deleting = index;
        },

        deleteOrConfirm(index) {
            if (this.data[index].key === null && this.data[index].value === null) {
                this.deleteValue(index);
            } else {
                this.confirmDeleteValue(index);
            }
        },

        deleteValue(index) {
            this.deleting = false;

            this.data.splice(index, 1);
        },

        toggleHidden(index) {
            this.data[index].hidden = !this.data[index].hidden;
        },

        deleteCancelled() {
            this.deleting = false;
        },

        setKey(key) {
            this.selectedKey = key;
        },
    },
};
</script>
