import { mount } from '@vue/test-utils';
import { expect, test, vi } from 'vitest';
import { ref } from 'vue';
import SectionInspector from '@/components/forms/builder/SectionInspector.vue';

const context = {};
vi.mock('@/pages/forms/Builder.vue', () => ({ injectBuilderContext: () => context }));

globalThis.__ = (key) => key;

test('a section without a display shows a placeholder without setting one', () => {
    const section = { _id: 'section-id', fields: [] };

    Object.assign(context, { dirty: () => {}, inspecting: ref(section) });

    const wrapper = mount(SectionInspector);

    expect(wrapper.find('a').text()).toBe('Section');
    expect(wrapper.find('input').attributes('placeholder')).toBe('Section');
    expect(wrapper.find('input').element.value).toBe('');
    expect(section).not.toHaveProperty('display');
});

test('a section with a display shows it', () => {
    Object.assign(context, { dirty: () => {}, inspecting: ref({ _id: 'section-id', display: 'Contact', fields: [] }) });

    const wrapper = mount(SectionInspector);

    expect(wrapper.find('a').text()).toBe('Contact');
    expect(wrapper.find('input').element.value).toBe('Contact');
});
