import { mount } from '@vue/test-utils';
import { expect, test } from 'vitest';
import SlugFieldtype from '@/components/fieldtypes/SlugFieldtype.vue';
import { publishContextKey } from '@/components/ui';

const blueprint = {
    tabs: [{ sections: [{ fields: [{ handle: 'display', display: 'Label' }] }] }],
};

test.each([
    ['the source field\'s label', 'display', 'Regenerate from: Label'],
    ['the handle when the source field is not in the blueprint', 'title', 'Regenerate from: title'],
])('the regenerate button tooltip shows %s', (_, from, tooltip) => {
    const wrapper = mount(SlugFieldtype, {
        props: {
            handle: 'handle',
            value: 'name',
            config: { from, show_regenerate: true },
        },
        global: {
            provide: { [publishContextKey]: { blueprint, values: {} } },
            mocks: {
                __: (key, { field }) => key.replace(':field', field),
                $events: { $on: () => {}, $off: () => {} },
            },
            directives: { tooltip: (el, binding) => el.setAttribute('data-tooltip', binding.value) },
            stubs: {
                slugify: { template: '<div><slot /></div>', data: () => ({ shouldSlugify: false }) },
                Input: { template: '<div><slot name="append" /></div>' },
                Button: { template: '<button />' },
            },
        },
    });

    expect(wrapper.find('button').attributes('data-tooltip')).toBe(tooltip);
});
