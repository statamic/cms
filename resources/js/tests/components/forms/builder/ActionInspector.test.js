import { mount } from '@vue/test-utils';
import { expect, test, vi } from 'vitest';
import { ref } from 'vue';
import ActionInspector from '@/components/forms/builder/ActionInspector.vue';

const fieldsets = {
    details: {
        handle: 'details',
        fields: [{ handle: 'company', type: 'inline', fieldtype: 'text', icon: 'text', config: { type: 'text', display: 'Company' } }],
    },
};

vi.mock('@inertiajs/vue3', async (importOriginal) => ({ ...(await importOriginal()), usePage: () => ({ props: { fieldsets } }) }));

const context = {};
vi.mock('@/pages/forms/Builder.vue', () => ({ injectBuilderContext: () => context }));

globalThis.__ = (key) => key;

test('fields from linked fieldsets are suggested for page rules with their prefix', () => {
    const page = {
        _id: 'first',
        rules: [],
        sections: [
            {
                fields: [
                    { _id: 'name-id', handle: 'name', type: 'inline', fieldtype: 'text', icon: 'text', config: { type: 'text', display: 'Name' } },
                    { _id: 'import-id', type: 'import', fieldset: 'details', prefix: 'work_' },
                    { _id: 'placeholder-id', type: 'link_fields' },
                ],
            },
        ],
    };

    Object.assign(context, {
        dirty: vi.fn(),
        fieldtypes: [],
        inspecting: ref(page),
        pages: ref([page, { _id: 'second', rules: [], sections: [] }]),
    });

    const wrapper = mount(ActionInspector, { shallow: true });

    expect(wrapper.vm.suggestableConditionFields.map((f) => f.handle)).toEqual(['name', 'work_company']);
});
