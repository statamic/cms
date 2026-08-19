<template>
    <div :class="{ 'w-full min-w-0': isCompact }">
        <component :is="wrapperComponent" v-bind="wrapperBinds">
            <template v-if="isCompact" #trigger>
                <ui-button
                    class="w-full min-w-0 shrink justify-between"
                    icon-append="chevron-down"
                    :read-only="isReadOnly"
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

            <div ref="editor">
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
                            <th class="">{{ valueHeader }}</th>
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
                                <td class="row-controls" v-if="!isReadOnly">
                                    <ui-button icon="x" variant="subtle" size="xs" round delete-action @click="deleteOrConfirm(index)" :aria-label="__('Delete Row')" v-tooltip="__('Delete Row')" />
                                </td>
                            </tr>
                        </tbody>
                    </sortable-list>
                </table>

                <div class="flex gap-2" :class="{ 'mt-2': isCompact && valueCount }">
                    <ui-button @click="addValue" :disabled="atMax" v-if="!isReadOnly && !isSingle && !isKeyed" :text="addButton" size="sm" />
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
            data: this.objectToSortable(this.value || []),
            selectedKey,
            deleting: false,
            compactOpen: false,
        };
    },

    watch: {
        data: {
            deep: true,
            handler(data) {
                this.updateDebounced(this.sortableToObject(data));
            },
        },

        value(value) {
            if (JSON.stringify(value) == JSON.stringify(this.sortableToObject(this.data))) return;
            this.data = this.objectToSortable(value);
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
        setCompactOpen(open) {
            this.compactOpen = open;

            if (open && !this.valueCount && !this.isReadOnly) {
                this.addValue();
            }
        },

        addValue() {
            this.data.push(this.newSortableValue());
            this.$nextTick(() => {
                this.$refs.editor?.querySelector('tr:last-child input')?.focus();
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

        deleteCancelled() {
            this.deleting = false;
        },

        setKey(key) {
            this.selectedKey = key;
        },
    },
};
</script>
