import { shallowMount } from '@vue/test-utils';
import { expect, test } from 'vitest';
import { defineComponent, h } from 'vue';
import PublishForm from '@/components/entries/PublishForm.vue';
import TermPublishForm from '@/components/terms/PublishForm.vue';
import SaveButtonOptions from '@/components/publish/SaveButtonOptions.vue';

window.__ = (key) => key;
window.Statamic = { $commandPalette: { add: () => {}, category: {}, remove: () => {} } };

const ContainerStub = defineComponent({ render: () => h('div') });

function mocks({ preference = null, afterSave = 'listing' } = {}) {
    return {
        $progress: { isComplete: () => true, loading: () => {} },
        $config: { get: (key, fallback) => (key === 'afterSave' ? afterSave : fallback) },
        $preferences: { get: () => preference, set: () => {} },
        $toast: { success: () => {}, error: () => {} },
        $keys: { bindGlobal: () => ({ destroy: () => {} }) },
        $events: { $on: () => {}, $off: () => {}, $emit: () => {} },
    };
}

function mountEntryForm(options) {
    return shallowMount(PublishForm, {
        props: {
            publishContainer: 'base',
            initialFieldset: { handle: 'speaker', tabs: [] },
            initialValues: {},
            initialMeta: {},
            initialLocalizations: [{ handle: 'default', active: true }],
            collectionHandle: 'speakers',
            initialActions: { save: '/save' },
            method: 'patch',
        },
        global: { stubs: { PublishContainer: ContainerStub }, mocks: mocks(options) },
    });
}

function mountTermForm(options) {
    return shallowMount(TermPublishForm, {
        props: {
            publishContainer: 'base',
            initialFieldset: { handle: 'tag', tabs: [] },
            initialValues: {},
            initialMeta: {},
            initialLocalizations: [{ handle: 'default', active: true }],
            taxonomyHandle: 'tags',
            initialActions: { save: '/save' },
            method: 'patch',
        },
        global: { stubs: { PublishContainer: ContainerStub }, mocks: mocks(options) },
    });
}

test.each([
    ['entry', mountEntryForm],
    ['term', mountTermForm],
])('%s form uses the configured after save default when there is no preference', (name, mountForm) => {
    expect(mountForm({ afterSave: 'continue_editing' }).vm.afterSaveOption).toBe('continue_editing');
    expect(mountForm({ afterSave: 'listing' }).vm.afterSaveOption).toBe('listing');
});

test.each([
    ['entry', mountEntryForm],
    ['term', mountTermForm],
])('%s form prefers the user preference over the configured default', (name, mountForm) => {
    const wrapper = mountForm({ preference: 'create_another', afterSave: 'continue_editing' });

    expect(wrapper.vm.afterSaveOption).toBe('create_another');
});

test('save button options default to the configured after save option', () => {
    const wrapper = shallowMount(SaveButtonOptions, {
        props: { preferencesPrefix: 'collections.speakers' },
        global: { mocks: mocks({ afterSave: 'continue_editing' }) },
    });

    expect(wrapper.vm.currentOption).toBe('continue_editing');
});

test('save button options prefer the user preference over the configured default', () => {
    const wrapper = shallowMount(SaveButtonOptions, {
        props: { preferencesPrefix: 'collections.speakers' },
        global: { mocks: mocks({ preference: 'create_another', afterSave: 'continue_editing' }) },
    });

    expect(wrapper.vm.currentOption).toBe('create_another');
});
