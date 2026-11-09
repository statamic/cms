import { shallowMount } from '@vue/test-utils';
import { describe, expect, test, vi } from 'vitest';
import * as Globals from '@/bootstrap/globals';
import LinkFields from '@/components/blueprints/LinkFields.vue';
import ImportField from '@/components/blueprints/ImportField.vue';
import ImportSettings from '@/components/fields/ImportSettings.vue';

const fieldsets = {
    sectioned: { handle: 'sectioned', title: 'Sectioned', fields: [], has_sections: true, sections_count: 2 },
    flat: { handle: 'flat', title: 'Flat', fields: [], has_sections: false, sections_count: 0 },
};

vi.mock('@inertiajs/vue3', () => ({
    usePage: () => ({ props: { fieldsets } }),
}));

Object.keys(Globals).forEach((fn) => (window[fn] = Globals[fn]));
window.__ = (key) => key;
window.__n = (key) => key;
window.cp_url = (url) => url;

const nestedContexts = [
    ['a replicator or bard set', { isInsideSet: true }],
    ['nested field settings (e.g. a grid or group)', { isInsideConfigFields: true }],
    ['a fieldset', { isInsideFieldset: true }],
];

function mount(component, { props = {}, provide = {} } = {}) {
    return shallowMount(component, {
        props,
        global: {
            provide,
            renderStubDefaultSlot: true,
            mocks: { $page: { props: { fieldsets } } },
        },
    });
}

describe('linking a fieldset', () => {
    function linkFieldset(handle, provide = {}) {
        const wrapper = mount(LinkFields, { provide });
        wrapper.vm.fieldset = handle;
        wrapper.vm.linkFieldset();

        return { wrapper, field: wrapper.emitted('linked')[0][0] };
    }

    test('offers section behavior for a sectioned fieldset at the top level of a blueprint', () => {
        const wrapper = mount(LinkFields);
        wrapper.vm.fieldset = 'sectioned';

        expect(wrapper.vm.canChooseSectionBehavior).toBe(true);
        expect(linkFieldset('sectioned').field.section_behavior).toBe('preserve');
    });

    test('does not offer section behavior for a flat fieldset', () => {
        const wrapper = mount(LinkFields);
        wrapper.vm.fieldset = 'flat';

        expect(wrapper.vm.canChooseSectionBehavior).toBe(false);
        expect(linkFieldset('flat').field).not.toHaveProperty('section_behavior');
    });

    test.each(nestedContexts)('does not offer section behavior inside %s', (_, provide) => {
        const wrapper = mount(LinkFields, { provide });
        wrapper.vm.fieldset = 'sectioned';

        expect(wrapper.vm.canChooseSectionBehavior).toBe(false);
        expect(linkFieldset('sectioned', provide).field).not.toHaveProperty('section_behavior');
    });
});

describe('import settings', () => {
    test('shows the section behavior control for a sectioned fieldset at the top level of a blueprint', () => {
        const wrapper = mount(ImportSettings, { props: { config: { fieldset: 'sectioned' } } });

        expect(wrapper.html()).toContain('Section Behavior');
    });

    test('hides the section behavior control for a flat fieldset', () => {
        const wrapper = mount(ImportSettings, { props: { config: { fieldset: 'flat' } } });

        expect(wrapper.html()).not.toContain('Section Behavior');
    });

    test.each(nestedContexts)('hides the section behavior control inside %s', (_, provide) => {
        const wrapper = mount(ImportSettings, { props: { config: { fieldset: 'sectioned' } }, provide });

        expect(wrapper.html()).not.toContain('Section Behavior');
    });

    test('hides the section behavior control when the isInsideSet prop is passed', () => {
        const wrapper = mount(ImportSettings, { props: { config: { fieldset: 'sectioned' }, isInsideSet: true } });

        expect(wrapper.html()).not.toContain('Section Behavior');
    });
});

describe('import field badge', () => {
    function badge(field, provide = {}) {
        return mount(ImportField, { props: { field: { _id: 'a', type: 'import', ...field } }, provide }).vm
            .sectionBadgeText;
    }

    test('shows sections are kept at the top level of a blueprint', () => {
        expect(badge({ fieldset: 'sectioned' })).toBe('Has Section|Has Sections');
    });

    test('shows sections are ignored when flattened at the top level of a blueprint', () => {
        expect(badge({ fieldset: 'sectioned', section_behavior: 'flatten' })).toBe('Ignoring Section|Ignoring Sections');
    });

    test('shows no badge for a flat fieldset', () => {
        expect(badge({ fieldset: 'flat' })).toBeNull();
    });

    test.each(nestedContexts)('shows sections are ignored inside %s, even when set to preserve', (_, provide) => {
        expect(badge({ fieldset: 'sectioned', section_behavior: 'preserve' }, provide)).toBe(
            'Ignoring Section|Ignoring Sections',
        );
    });
});
