import { mount, flushPromises } from '@vue/test-utils';
import { expect, test, vi } from 'vitest';
import { ref } from 'vue';
import axios from 'axios';
import FieldInspector from '@/components/forms/builder/FieldInspector.vue';
import FieldConditionsBuilder from '@/components/field-conditions/Builder.vue';

vi.mock('axios');
vi.mock('@ui', async (importOriginal) => {
    const passthrough = { template: '<div><slot /></div>' };
    return { ...(await importOriginal()), PublishContainer: passthrough, PublishFieldsProvider: passthrough, PublishFields: passthrough };
});
vi.mock('@/util/debounce', () => ({ default: (fn) => Object.assign(fn, { cancel: () => {} }) }));

const context = {};
vi.mock('@/pages/forms/Builder.vue', () => ({ injectBuilderContext: () => context }));

globalThis.__ = (key) => key;
globalThis.cp_url = (url) => url;

test('switching the condition key in the inspector drops the old key', async () => {
    const field = {
        _id: 'phone-id',
        handle: 'phone',
        fieldtype: 'text',
        icon: 'text',
        config: { type: 'text', display: 'Phone', if: { method: 'equals phone' } },
    };

    Object.assign(context, {
        dirty: () => {},
        errors: ref({}),
        fieldtypes: [{ handle: 'radio', categories: ['choice'] }, { handle: 'text', categories: ['text'] }],
        form: { handle: 'contact' },
        inspecting: ref(field),
        pages: ref([
            {
                sections: [
                    {
                        fields: [
                            { _id: 'method-id', handle: 'method', fieldtype: 'radio', config: { type: 'radio', display: 'Method', options: { email: 'Email', phone: 'Phone' } } },
                            field,
                        ],
                    },
                ],
            },
        ]),
        showFieldDirection: false,
        withoutDirtying: (callback) => callback(),
    });

    axios.post.mockImplementation((url, payload) => {
        if (url.endsWith('/edit')) {
            return Promise.resolve({
                data: {
                    fieldtype: {},
                    blueprint: { tabs: [{ sections: [{ fields: [] }] }] },
                    values: { ...payload.values },
                    meta: {},
                    originValues: {},
                    originMeta: {},
                },
            });
        }

        return Promise.resolve({ data: { values: { ...payload.values } } });
    });

    const wrapper = mount(FieldInspector, {
        global: { stubs: { FieldValidationBuilder: true } },
    });
    await flushPromises();

    wrapper.findComponent(FieldConditionsBuilder).vm.when = 'unless';
    await flushPromises();

    expect(wrapper.vm.values).toMatchObject({ unless: { method: 'equals phone' } });
    expect(wrapper.vm.values).not.toHaveProperty('if');

    const update = axios.post.mock.calls.filter(([url]) => url.endsWith('/update')).at(-1)[1];
    expect(update.values).toMatchObject({ unless: { method: 'equals phone' } });
    expect(update.values).not.toHaveProperty('if');
    expect(field.config).not.toHaveProperty('if');
});
