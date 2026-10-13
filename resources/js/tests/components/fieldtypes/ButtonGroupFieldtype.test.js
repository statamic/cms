import { mount } from '@vue/test-utils';
import { expect, test } from 'vitest';
import ButtonGroupFieldtype from '@/components/fieldtypes/ButtonGroupFieldtype.vue';

window.ResizeObserver = class {
    observe() {}
    unobserve() {}
    disconnect() {}
};

const options = [
    { key: 'option_1', value: 'Option 1' },
    { key: 'option_2', value: 'Option 2' },
];

test('value can be updated', async () => {
    const wrapper = mount(ButtonGroupFieldtype, {
        props: {
            value: 'option_1',
            handle: 'button_group',
            config: { options },
        },
    });

    await wrapper.findAll('button')[1].trigger('click');
    expect(wrapper.emitted('update:value')[0]).toEqual(['option_2']);
});

test('value cannot be updated when read only', async () => {
    const wrapper = mount(ButtonGroupFieldtype, {
        props: {
            value: 'option_1',
            handle: 'button_group',
            config: { options },
            readOnly: true,
        },
    });

    await wrapper.findAll('button')[1].trigger('click');
    expect(wrapper.emitted('update:value')).toBeUndefined();
});
