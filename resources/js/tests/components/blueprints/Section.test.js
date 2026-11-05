import { shallowMount } from '@vue/test-utils';
import { beforeEach, expect, test, vi } from 'vitest';
import Section from '@/components/blueprints/Section.vue';

beforeEach(() => {
    global.clone = (value) => JSON.parse(JSON.stringify(value));
    global.snake_case = (value) => value.toLowerCase().replaceAll(' ', '_');
});

function mountSection(extraConfig = undefined, setConfig = null) {
    return shallowMount(Section, {
        props: {
            canDefineLocalizable: false,
            section: { _id: 'hero', handle: 'hero', display: 'Hero', fields: [], ...(extraConfig && { extraConfig }) },
        },
        global: {
            provide: { setConfig },
            mocks: {
                $config: { get: () => null },
                $stacks: { stacks: () => [] },
                $keys: { bindGlobal: () => ({ destroy: vi.fn() }) },
            },
            stubs: { 'ui-stack': true, Fields: true },
        },
    });
}

test('cancelling nested config edits leaves the original set unchanged', async () => {
    const config = { values: { addon: { note: 'Original' } }, meta: {} };
    const wrapper = mountSection(config);
    wrapper.vm.edit();
    wrapper.vm.editingSection.extraConfig.values.addon.note = 'Changed';
    await wrapper.vm.$nextTick();
    wrapper.vm.editCancelled();
    expect(wrapper.props('section').extraConfig.values.addon.note).toBe('Original');
    wrapper.vm.edit();
    expect(wrapper.vm.editingSection.extraConfig.values.addon.note).toBe('Original');
    wrapper.unmount();
});

test('confirming includes edited config in the updated set', () => {
    const wrapper = mountSection({ values: { addon: { note: 'Original' } }, meta: {} });
    wrapper.vm.edit();
    wrapper.vm.editingSection.extraConfig.values.addon.note = 'Changed';
    wrapper.vm.editConfirmed();
    expect(wrapper.emitted('updated').at(-1)[0].extraConfig.values.addon.note).toBe('Changed');
    wrapper.unmount();
});

test('new sets get independent copies of registered defaults', () => {
    const setConfig = { fields: [{ handle: 'addon' }], defaults: { values: { addon: { note: 'Default' } }, meta: {} } };
    const wrapper = mountSection(undefined, setConfig);
    wrapper.vm.edit();
    wrapper.vm.editingSection.extraConfig.values.addon.note = 'Changed';
    expect(setConfig.defaults.values.addon.note).toBe('Default');
    wrapper.unmount();
});

test('ordinary blueprint sections do not acquire set configuration', () => {
    const wrapper = mountSection();
    wrapper.vm.edit();
    expect(wrapper.vm.editingSection).not.toHaveProperty('extraConfig');
    wrapper.unmount();
});
