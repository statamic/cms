import { test, expect } from 'vitest';
import { mount } from '@vue/test-utils';
import { nextTick } from 'vue';
import PublishContainer from '@ui/Publish/Container.vue';

window.Statamic = {
    $dirty: {
        add: () => {},
        remove: () => {},
        has: () => false,
    },
    $events: {
        $emit: () => {},
    },
    $config: {
        get: (key) => (key === 'sites' ? [{ handle: 'default', direction: 'ltr' }] : undefined),
    },
};
window.__ = (msg) => msg;

const TestComponent = { template: '<div></div>' };

const createContainer = (props = {}) => mount(PublishContainer, {
    props: {
        blueprint: { publish: [], tabs: { main: { fields: [] } } },
        modelValue: {},
        trackDirtyState: false,
        ...props,
    },
    slots: { default: TestComponent },
});

test('it returns values by reference when no hidden fields', () => {
    const values = { title: 'Hello', body: 'World' };
    const wrapper = createContainer({ modelValue: values });

    expect(wrapper.vm.visibleValues).toBe(wrapper.vm.values);
});

test('it omits top-level keys with omitValue true', async () => {
    const values = { title: 'Hello', secret: 'hidden', body: 'World' };
    const wrapper = createContainer({ modelValue: values });

    wrapper.vm.setHiddenField({ dottedKey: 'secret', hidden: true, omitValue: true });
    await nextTick();

    expect(wrapper.vm.visibleValues).not.toHaveProperty('secret');
    expect(wrapper.vm.visibleValues).toHaveProperty('title', 'Hello');
    expect(wrapper.vm.visibleValues).toHaveProperty('body', 'World');
    expect(wrapper.vm.values).toHaveProperty('secret', 'hidden');
});

test('it omits nested paths correctly', async () => {
    const values = {
        features: [
            { textcontent: 'Feature 1', price: 100 },
            { textcontent: 'Feature 2', price: 200 },
        ],
    };
    const wrapper = createContainer({ modelValue: values });

    wrapper.vm.setHiddenField({ dottedKey: 'features.0.textcontent', hidden: true, omitValue: true });
    await nextTick();

    expect(wrapper.vm.visibleValues.features[0]).not.toHaveProperty('textcontent');
    expect(wrapper.vm.visibleValues.features[0]).toHaveProperty('price', 100);
    expect(wrapper.vm.visibleValues.features[1]).toHaveProperty('textcontent', 'Feature 2');
});

test('it returns values by reference when hidden field changes to not omit', async () => {
    const values = { title: 'Hello', secret: 'hidden' };
    const wrapper = createContainer({ modelValue: values });

    wrapper.vm.setHiddenField({ dottedKey: 'secret', hidden: true, omitValue: true });
    await nextTick();
    expect(wrapper.vm.visibleValues).not.toBe(wrapper.vm.values);

    wrapper.vm.setHiddenField({ dottedKey: 'secret', hidden: true, omitValue: false });
    await nextTick();
    expect(wrapper.vm.visibleValues).toBe(wrapper.vm.values);
});

test('it emits update:visibleValues on deep changes', async () => {
    const values = { title: 'Hello', nested: { key: 'value' } };
    const wrapper = createContainer({ modelValue: values });

    wrapper.emitted()['update:visibleValues'] = [];

    wrapper.vm.values.nested.key = 'updated';
    await nextTick();

    expect(wrapper.emitted()['update:visibleValues']).toBeTruthy();
    expect(wrapper.emitted()['update:visibleValues'].length).toBeGreaterThan(0);
});
