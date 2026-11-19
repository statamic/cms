import { flushPromises, mount } from '@vue/test-utils';
import { expect, test, vi } from 'vitest';
import * as Globals from '@/bootstrap/globals';
import FormGroupFieldsFieldtype from '@/components/fieldtypes/FormGroupFieldsFieldtype.vue';
import FieldtypeSelector from '@/components/fields/FieldtypeSelector.vue';
import Settings from '@/components/fields/Settings.vue';

vi.mock('@shopify/draggable', () => ({
    Sortable: class {
        on() {
            return this;
        }
    },
    Plugins: {},
}));

Object.keys(Globals).forEach((fn) => (window[fn] = Globals[fn]));
window.__ = (key) => key;
window.cp_url = (url) => `/cp/${url}`;

const formFieldtype = (handle, extra = {}) => ({
    handle,
    title: handle,
    icon: `${handle}-icon`,
    categories: ['text'],
    keywords: [],
    config: [],
    ...extra,
});

const builderFieldtypes = [
    formFieldtype('short_answer', { config: [{ handle: 'placeholder', default: null }, { handle: 'character_limit', default: 50 }] }),
    formFieldtype('toggle', { categories: ['choice'] }),
    formFieldtype('group', { categories: ['structure'] }),
    formFieldtype('upload', { categories: ['media'] }),
    formFieldtype('assets', { categories: [] }),
    formFieldtype('files', { categories: [] }),
];

function mountWithin(builder, axios = {}) {
    const wrapper = mount(FormGroupFieldsFieldtype, {
        shallow: true,
        props: { handle: 'fields', value: [], config: {}, meta: {} },
        global: {
            provide: builder ? { FormBuilderContext: builder } : {},
            mocks: { $axios: axios, $toast: { success: () => {} } },
        },
    });

    const provides = wrapper.vm.$.provides;

    return {
        wrapper,
        injected: {
            source: provides.fieldtypeSelectorSource ?? null,
            api: provides.fieldSettingsApi ?? null,
        },
    };
}

const builder = { form: { handle: 'contact' }, fieldtypes: builderFieldtypes };

test('the child picker only offers form fieldtypes that can live inside a group', () => {
    const { injected } = mountWithin(builder);

    expect(injected.source.fieldtypes.map((fieldtype) => fieldtype.handle)).toEqual(['short_answer', 'toggle']);
    expect(Object.keys(injected.source.categories)).toContain('choice');
    expect(injected.source.defaultCategory).toBe('other');
});

test('new children are created with form field config', () => {
    const { injected } = mountWithin(builder);

    const { icon, config } = injected.source.createField(builderFieldtypes[0]);

    expect(icon).toBe('short_answer-icon');
    expect(config).toEqual({
        type: 'short_answer',
        display: 'short_answer',
        handle: null,
        instructions: null,
        width: 100,
        isNew: true,
        placeholder: null,
        character_limit: 50,
    });
});

test('child settings are loaded and committed through the form builder endpoints', async () => {
    const post = vi.fn((url) =>
        Promise.resolve({
            data: url.endsWith('/edit') ? { fieldtype: {} } : { values: { handle: 'city' }, preview: {} },
        }),
    );

    const { injected } = mountWithin(builder, { post });

    expect(injected.api.canSaveRoot).toBe(false);

    expect(await injected.api.edit({ type: 'short_answer' })).toEqual({ fieldtype: {} });
    expect(post).toHaveBeenLastCalledWith('/cp/forms/contact/builder/fields/edit', { type: 'short_answer' });

    expect(await injected.api.update({ type: 'short_answer' })).toEqual({ handle: 'city' });
    expect(post).toHaveBeenLastCalledWith('/cp/forms/contact/builder/fields/update', { type: 'short_answer' });
});

test('outside the form builder it behaves like the core fields fieldtype', () => {
    const { injected } = mountWithin(null);

    expect(injected.source).toBeNull();
    expect(injected.api).toBeNull();
});

test('the fieldtype selector uses an injected source instead of fetching core fieldtypes', async () => {
    const get = vi.fn(() => Promise.resolve({ data: [] }));
    const createField = vi.fn((fieldtype) => ({ icon: fieldtype.icon, config: { type: fieldtype.handle } }));

    const wrapper = mount(FieldtypeSelector, {
        global: {
            provide: {
                fieldtypeSelectorSource: {
                    fieldtypes: [formFieldtype('short_answer'), formFieldtype('yes_no', { categories: [] })],
                    categories: { text: { title: 'Text' }, other: { title: 'Other' } },
                    defaultCategory: 'other',
                    createField,
                },
            },
            mocks: { $axios: { get }, $toast: { error: () => {} } },
            stubs: { 'ui-input': true, 'ui-panel': true, 'ui-panel-header': true, 'ui-description': true, 'ui-icon': true },
        },
    });

    await flushPromises();

    expect(get).not.toHaveBeenCalled();
    expect(wrapper.vm.groupedFieldtypes.text.fieldtypes.map((f) => f.value)).toEqual(['short_answer']);
    expect(wrapper.vm.groupedFieldtypes.other.fieldtypes.map((f) => f.value)).toEqual(['yes_no']);

    wrapper.vm.select({ value: 'short_answer' });

    expect(createField).toHaveBeenCalledWith(expect.objectContaining({ handle: 'short_answer' }));
    expect(wrapper.emitted('selected')[0][0]).toEqual({ icon: 'short_answer-icon', config: { type: 'short_answer' } });
});

test('field settings use an injected api and only apply when the root form cannot be saved', async () => {
    const edit = vi.fn(() =>
        Promise.resolve({
            fieldtype: { icon: 'x' },
            blueprint: { tabs: [{ sections: [{ fields: [] }] }] },
            values: { display: 'City', handle: 'city' },
            meta: {},
            originValues: null,
            originMeta: null,
        }),
    );
    const update = vi.fn(() => Promise.resolve({ display: 'City', handle: 'city', type: 'short_answer' }));
    const emit = vi.fn();

    const wrapper = mount(Settings, {
        props: { type: 'short_answer', config: { handle: 'city' }, fields: [], id: 'one' },
        global: {
            provide: { fieldSettingsApi: { canSaveRoot: false, edit, update } },
            mocks: {
                $axios: { post: vi.fn() },
                $keys: { bindGlobal: () => ({ destroy: () => {} }) },
                $events: { $emit: emit },
                $toast: { error: () => {} },
            },
            stubs: { StackHeader: true, StackContent: true },
        },
    });

    await flushPromises();

    expect(edit).toHaveBeenCalledWith({ type: 'short_answer', reference: false, values: { handle: 'city' } });
    expect(wrapper.vm.canSaveRoot).toBe(false);

    wrapper.vm.commitAndSave();
    await flushPromises();

    expect(update).toHaveBeenCalledWith(expect.objectContaining({ id: 'one', type: 'short_answer', fields: [] }));
    expect(wrapper.emitted('committed')[0][0]).toEqual({ display: 'City', handle: 'city', type: 'short_answer' });
    expect(emit).not.toHaveBeenCalledWith('root-form-save');
});
