import { mount } from '@vue/test-utils';
import { expect, test, vi } from 'vitest';
import Builder from '@/components/field-validation/Builder.vue';

vi.mock('@inertiajs/vue3', () => ({
    usePage: () => ({ props: { extensionRules: [] } }),
}));

globalThis.__ = (key) => key;
globalThis.__n = (key) => key;
globalThis.clone = (value) => structuredClone(value);

const mountBuilder = () =>
    mount(Builder, {
        props: { config: {} },
        global: {
            mocks: {
                $config: { get: () => '12.0.0' },
            },
            stubs: {
                SortableList: true,
            },
        },
    });

test('a typed rule parameter is committed when the input loses focus', async () => {
    const wrapper = mountBuilder();

    // Picking a parameterized rule swaps the combobox for a plain input holding "after:"
    wrapper.vm.add('after:');
    await wrapper.vm.$nextTick();

    const input = wrapper.find('input');
    await input.setValue('after:{this}.start_time');
    await input.trigger('focusout');

    expect(wrapper.emitted('updated').at(-1)[0]).toEqual(['after:{this}.start_time']);
});

test('an unfinished rule is not committed when the input loses focus', async () => {
    const wrapper = mountBuilder();

    wrapper.vm.add('after:');
    await wrapper.vm.$nextTick();

    const emitsBefore = wrapper.emitted('updated')?.length ?? 0;
    await wrapper.find('input').trigger('focusout');

    expect(wrapper.emitted('updated')?.length ?? 0).toBe(emitsBefore);
});
