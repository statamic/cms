import { mount } from '@vue/test-utils';
import { expect, test } from 'vitest';
import { h, nextTick, toRaw } from 'vue';
import * as Globals from '@/bootstrap/globals';
import Container from '@/components/ui/Publish/Container.vue';
import Fields from '@/components/ui/Publish/Fields.vue';
import FieldsProvider from '@/components/ui/Publish/FieldsProvider.vue';
import Values from '@/components/publish/Values.js';

Object.keys(Globals).forEach((fn) => (window[fn] = Globals[fn]));
window.__ = (key) => key;

window.Statamic = {
    $app: { component: () => undefined },
    $config: {
        get: (key) => (key === 'sites' ? [{ handle: 'default', direction: 'ltr' }] : undefined),
    },
    $dirty: { has: () => false, add: () => {}, remove: () => {} },
    $events: { $emit: () => {} },
};

const unconditionalFields = [{ handle: 'title', type: 'text' }];

const conditionalFields = [
    { handle: 'toggle', type: 'toggle' },
    { handle: 'secret', type: 'text', if: { toggle: 'equals true' } },
];

// Field conditions commit their omit bookkeeping on a later tick, so let the queue drain.
async function settle() {
    for (let i = 0; i < 5; i++) await nextTick();
}

async function mountContainer(modelValue, fields = unconditionalFields) {
    const emitted = [];

    const wrapper = mount(Container, {
        props: {
            blueprint: { tabs: [] },
            modelValue,
            site: 'default',
            // Snapshot on receipt. On the uncloned path the payload is the live tree, so
            // holding the reference would let later edits rewrite earlier emissions.
            'onUpdate:visibleValues': (values) => emitted.push(structuredClone(toRaw(values))),
        },
        slots: { default: () => [h(FieldsProvider, { fields }, () => h(Fields))] },
    });

    await settle();

    return { wrapper, emitted };
}

test('visible values are the live values tree when nothing is omitted', async () => {
    const { wrapper } = await mountContainer({ title: 'Hello' });

    expect(wrapper.vm.visibleValues).toBe(wrapper.vm.values);
});

test('visible values are a copy when a field is omitted', async () => {
    const { wrapper } = await mountContainer({ toggle: false, secret: 'shh' }, conditionalFields);

    expect(wrapper.vm.visibleValues).not.toBe(wrapper.vm.values);
    expect(wrapper.vm.visibleValues).toEqual({ toggle: false });
    expect(wrapper.vm.values).toEqual({ toggle: false, secret: 'shh' });
});

test('the uncloned tree has the same content as a clone would', async () => {
    const { wrapper } = await mountContainer({ title: 'Hello', nested: { deep: ['a', 'b'] }, empty: null });

    expect(wrapper.vm.visibleValues).toEqual(new Values(wrapper.vm.values).except([]));
});

test('the uncloned tree serializes identically to a clone', async () => {
    const values = { title: 'Hello', nested: { deep: ['a', 'b'] }, empty: null, missing: undefined };
    const { wrapper } = await mountContainer(values);

    expect(JSON.stringify(wrapper.vm.visibleValues)).toBe(JSON.stringify(new Values(wrapper.vm.values).except([])));
});

test('visible values are emitted when a value changes', async () => {
    const { wrapper, emitted } = await mountContainer({ title: 'Hello' });
    const before = emitted.length;

    wrapper.vm.setFieldValue('title', 'Goodbye');
    await settle();

    expect(emitted.length).toBe(before + 1);
    expect(emitted.at(-1)).toEqual({ title: 'Goodbye' });
});

test('visible values are emitted when a nested value changes', async () => {
    const { wrapper, emitted } = await mountContainer({ nested: { deep: 'a' } });
    const before = emitted.length;

    wrapper.vm.setFieldValue('nested.deep', 'b');
    await settle();

    expect(emitted.length).toBe(before + 1);
    expect(emitted.at(-1)).toEqual({ nested: { deep: 'b' } });
});

test('visible values are emitted when the values are replaced wholesale', async () => {
    const { wrapper, emitted } = await mountContainer({ title: 'Hello' });
    const before = emitted.length;

    wrapper.vm.setValues({ title: 'Replaced' });
    await settle();

    expect(emitted.length).toBe(before + 1);
    expect(emitted.at(-1)).toEqual({ title: 'Replaced' });
});

test('visible values are emitted when a field becomes omitted', async () => {
    const { wrapper, emitted } = await mountContainer({ toggle: true, secret: 'shh' }, conditionalFields);

    wrapper.vm.setFieldValue('toggle', false);
    await settle();

    expect(emitted.at(-1)).toEqual({ toggle: false });
    expect(wrapper.vm.visibleValues).toEqual({ toggle: false });
});

test('visible values are emitted when a field stops being omitted', async () => {
    const { wrapper, emitted } = await mountContainer({ toggle: false, secret: 'shh' }, conditionalFields);

    expect(wrapper.vm.visibleValues).toEqual({ toggle: false });

    wrapper.vm.setFieldValue('toggle', true);
    await settle();

    expect(emitted.at(-1)).toEqual({ toggle: true, secret: 'shh' });
    expect(wrapper.vm.visibleValues).toEqual({ toggle: true, secret: 'shh' });
});
