import { mount, flushPromises } from '@vue/test-utils';
import { expect, test } from 'vitest';
import { reactive } from 'vue';
import Builder from '@/components/field-conditions/Builder.vue';

globalThis.__ = (key) => key;

const suggestableFields = [
    { handle: 'name', config: { type: 'text', display: 'Name' } },
    { handle: 'age', config: { type: 'text', display: 'Age' } },
];

const mountBuilder = async (config = {}, props = {}) => {
    const wrapper = mount(Builder, { props: { config, suggestableFields, ...props } });
    await flushPromises();
    return wrapper;
};

const lastEmitted = (wrapper) => wrapper.emitted('updated').at(-1)[0];

test('only the active condition key is emitted', async () => {
    const wrapper = await mountBuilder({ handle: 'email', unless: { name: 'bob' } });

    wrapper.vm.conditions[0].value = 'alice';
    await flushPromises();

    expect(wrapper.emitted('updated')).toHaveLength(1);
    expect(lastEmitted(wrapper)).toEqual({ unless: { name: 'equals alice' } });
});

test('switching between show and hide emits only the new key', async () => {
    const wrapper = await mountBuilder({ handle: 'email', if: { name: 'bob' } });

    wrapper.vm.when = 'unless';
    await flushPromises();

    expect(wrapper.emitted('updated')).toHaveLength(1);
    expect(lastEmitted(wrapper)).toEqual({ unless: { name: 'equals bob' } });
});

test('any conditions emit only the _any key', async () => {
    const wrapper = await mountBuilder({ handle: 'email', if: { name: 'bob' } });

    wrapper.vm.type = 'any';
    await flushPromises();

    expect(lastEmitted(wrapper)).toEqual({ if_any: { name: 'equals bob' } });
});

test('always show emits no condition keys', async () => {
    const wrapper = await mountBuilder({ handle: 'email', if: { name: 'bob' } });

    wrapper.vm.when = 'always';
    await flushPromises();

    expect(lastEmitted(wrapper)).toEqual({});
});

test('hidden is not emitted when the always hide option is not shown', async () => {
    const wrapper = await mountBuilder({ handle: 'email', if: { name: 'bob' } });

    wrapper.vm.when = 'always';
    await flushPromises();

    wrapper.emitted('updated').forEach(([conditions]) => expect(conditions).not.toHaveProperty('hidden'));
});

test('hidden is emitted together with the conditions when the always hide option is shown', async () => {
    const wrapper = await mountBuilder({ handle: 'email', if: { name: 'bob' } }, { showAlwaysHideOption: true });

    wrapper.vm.when = 'unless';
    await flushPromises();

    expect(wrapper.emitted('updated')).toHaveLength(1);
    expect(lastEmitted(wrapper)).toEqual({ unless: { name: 'equals bob' }, hidden: false });

    wrapper.vm.when = 'always_hide';
    await flushPromises();

    expect(wrapper.emitted('updated')).toHaveLength(2);
    expect(lastEmitted(wrapper)).toEqual({ hidden: true });
});

test('incomplete conditions emit no condition keys', async () => {
    const wrapper = await mountBuilder({ handle: 'email' }, { showAlwaysHideOption: true });

    wrapper.vm.when = 'if';
    wrapper.vm.conditions[0].field = 'name';
    await flushPromises();

    expect(lastEmitted(wrapper)).toEqual({ hidden: false });
});

test('hiding a field from outside keeps its conditions and restores them when shown again', async () => {
    const config = reactive({ handle: 'email', if: { name: 'bob' } });
    const wrapper = await mountBuilder(config, { showAlwaysHideOption: true });

    config.hidden = true;
    await flushPromises();

    expect(wrapper.vm.when).toBe('always_hide');
    expect(lastEmitted(wrapper)).toEqual({ if: { name: 'equals bob' }, hidden: true });

    config.hidden = false;
    await flushPromises();

    expect(wrapper.vm.when).toBe('if');
    expect(lastEmitted(wrapper)).toEqual({ if: { name: 'equals bob' }, hidden: false });
});

test('hiding a field from outside keeps any and hide conditions', async () => {
    const config = reactive({ handle: 'email', unless_any: { name: 'bob', age: 'equals 3' } });
    const wrapper = await mountBuilder(config, { showAlwaysHideOption: true });

    config.hidden = true;
    await flushPromises();

    expect(lastEmitted(wrapper)).toEqual({ unless_any: { name: 'equals bob', age: 'equals 3' }, hidden: true });

    config.hidden = false;
    await flushPromises();

    expect(wrapper.vm.when).toBe('unless');
    expect(wrapper.vm.type).toBe('any');
});

test('a hidden field with conditions loads as always hide and restores the conditions when shown', async () => {
    const config = reactive({ handle: 'email', hidden: true, if: { name: 'bob' } });
    const wrapper = await mountBuilder(config, { showAlwaysHideOption: true });

    expect(wrapper.vm.when).toBe('always_hide');
    expect(wrapper.emitted('updated')).toBeUndefined();

    config.hidden = false;
    await flushPromises();

    expect(wrapper.vm.when).toBe('if');
    expect(lastEmitted(wrapper)).toEqual({ if: { name: 'equals bob' }, hidden: false });
});

test('choosing a different option while hidden from outside discards the kept conditions', async () => {
    const config = reactive({ handle: 'email', if: { name: 'bob' } });
    const wrapper = await mountBuilder(config, { showAlwaysHideOption: true });

    config.hidden = true;
    await flushPromises();

    wrapper.vm.when = 'always';
    await flushPromises();

    expect(lastEmitted(wrapper)).toEqual({ hidden: false });

    wrapper.vm.when = 'always_hide';
    await flushPromises();

    expect(lastEmitted(wrapper)).toEqual({ hidden: true });
});

test('without the always hide option, hiding from outside behaves as before', async () => {
    const config = reactive({ handle: 'email', if: { name: 'bob' } });
    const wrapper = await mountBuilder(config);

    config.hidden = true;
    await flushPromises();

    expect(wrapper.vm.when).toBe('always_hide');
    expect(lastEmitted(wrapper)).toEqual({});

    config.hidden = false;
    await flushPromises();

    expect(wrapper.vm.when).toBe('always');
});
