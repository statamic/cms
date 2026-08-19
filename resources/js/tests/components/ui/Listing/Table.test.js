import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import { defineComponent, ref } from 'vue';
import { mount } from '@vue/test-utils';
import { provideListingContext } from '@/components/ui/Listing/Listing.vue';
import TableBody from '@/components/ui/Listing/TableBody.vue';
import ToggleAll from '@/components/ui/Listing/ToggleAll.vue';

const SlotStub = defineComponent({
    template: '<slot />',
});

const CheckboxStub = defineComponent({
    emits: ['update:modelValue'],
    template: '<button type="button" @click="$emit(\'update:modelValue\')" />',
});

describe('grouped listing tables', () => {
    beforeEach(() => {
        globalThis.__ = (key) => key;
        globalThis.Statamic = {
            $config: { get: vi.fn((key, fallback) => fallback) },
            $events: { $on: vi.fn(), $off: vi.fn() },
            $progress: { loading: vi.fn(), complete: vi.fn() },
            $toast: { error: vi.fn() },
        };
    });

    afterEach(() => {
        delete globalThis.__;
        delete globalThis.Statamic;
    });

    it('limits shift selection to the current table', async () => {
        const Harness = defineComponent({
            components: { TableBody },
            setup() {
                const items = ref([
                    { id: 'one', title: 'One' },
                    { id: 'two', title: 'Two' },
                    { id: 'three', title: 'Three' },
                ]);
                const selections = ref([]);

                provideListingContext({
                    items,
                    selections,
                    reorderable: ref(false),
                    reordered: vi.fn(),
                    visibleColumns: ref([{ field: 'title' }]),
                    hasActions: ref(false),
                    allowsSelections: ref(true),
                    selectRange: vi.fn(),
                    selectionClicked: vi.fn(),
                    toggleSelection(id) {
                        const index = selections.value.indexOf(id);

                        if (index === -1) {
                            selections.value.push(id);
                            return;
                        }

                        selections.value.splice(index, 1);
                    },
                    hasReachedSelectionLimit: ref(false),
                    allowsMultipleSelections: ref(true),
                    isColumnVisible: vi.fn(() => true),
                });

                return {
                    group: [items.value[2], items.value[0]],
                    selections,
                };
            },
            template: '<table><TableBody :items="group" /></table>',
        });

        const wrapper = mount(Harness, {
            global: {
                stubs: {
                    Checkbox: true,
                    SortableList: SlotStub,
                    TableField: true,
                },
            },
        });
        const rows = wrapper.findAll('tr');

        await rows[0].trigger('click');
        await rows[1].trigger('click', { shiftKey: true });

        expect(wrapper.vm.selections).toEqual(['three', 'one']);

        wrapper.unmount();
    });

    it('toggles only the items in the current table', async () => {
        const Harness = defineComponent({
            components: { ToggleAll },
            setup() {
                const items = ref([
                    { id: 'one', title: 'One' },
                    { id: 'two', title: 'Two' },
                    { id: 'three', title: 'Three' },
                ]);
                const selections = ref(['three']);

                provideListingContext({
                    items,
                    selections,
                    maxSelections: ref(Infinity),
                    clearSelections: () => selections.value.splice(0),
                    reorderable: ref(false),
                });

                return {
                    group: items.value.slice(0, 2),
                    selections,
                };
            },
            template: '<ToggleAll :items="group" />',
        });

        const wrapper = mount(Harness, {
            global: {
                stubs: {
                    Checkbox: CheckboxStub,
                },
            },
        });

        await wrapper.find('button').trigger('click');
        expect(wrapper.vm.selections).toEqual(['three', 'one', 'two']);

        await wrapper.find('button').trigger('click');
        expect(wrapper.vm.selections).toEqual(['three']);

        wrapper.unmount();
    });
});
