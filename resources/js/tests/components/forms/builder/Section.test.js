import { mount } from '@vue/test-utils';
import { expect, test, vi } from 'vitest';
import { ref } from 'vue';
import Section from '@/components/forms/builder/Section.vue';

const context = {};
vi.mock('@/pages/forms/Builder.vue', () => ({
    injectBuilderContext: () => context,
    FieldView: { Expanded: 'expanded', Collapsed: 'collapsed' },
    InspectorType: { Section: 'section', Field: 'field', LinkFields: 'link_fields' },
}));

globalThis.__ = (key) => key;

const setup = (fields) => {
    const section = { _id: 'section', display: 'Section', fields };

    Object.assign(context, {
        clearInspector: vi.fn(),
        dirty: vi.fn(),
        ensureUniqueDisplay: (display) => display,
        fieldtypes: [],
        fieldView: ref('expanded'),
        inspect: vi.fn(),
        inspecting: ref(null),
        inspectorType: ref(null),
        pages: ref([{ sections: [section] }]),
    });

    const wrapper = mount(Section, {
        props: { section, canDeleteSection: true },
        shallow: true,
        global: { renderStubDefaultSlot: true },
    });

    return { wrapper, section };
};

test('removing a linked fieldset asks for confirmation', async () => {
    const field = { _id: 'import', type: 'import', fieldset: 'details' };
    const { wrapper } = setup([field]);

    wrapper.findComponent({ name: 'ImportField' }).vm.$emit('remove');

    expect(wrapper.vm.fieldPendingDeletion).toStrictEqual(field);
    expect(wrapper.vm.confirmingFieldDeletion).toBe(true);
});

test('changing the width of a linked field adds a width override', () => {
    const field = { _id: 'company', type: 'reference', handle: 'company', fieldtype: 'text', config: { type: 'text', width: 100 }, config_overrides: ['display'] };
    const { wrapper } = setup([field]);

    wrapper.findComponent({ name: 'RegularFormField' }).vm.$emit('width-changed', 50);

    expect(field.config.width).toBe(50);
    expect(field.config_overrides).toEqual(['display', 'width']);
});
