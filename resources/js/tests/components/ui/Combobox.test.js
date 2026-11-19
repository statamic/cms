import { mount } from '@vue/test-utils';
import { expect, test } from 'vitest';
import { Combobox } from '@/components/ui';

window.__ = (key) => key;

const options = [{ label: 'Alfa', value: 'alfa' }];

test('trigger is exposed as a combobox when there is no search input', () => {
    const wrapper = mount(Combobox, { props: { options, modelValue: 'alfa' } });

    expect(wrapper.get('[data-ui-combobox-trigger]').attributes()).toMatchObject({
        role: 'combobox',
        tabindex: '0',
    });
});

test('trigger gives up its role and tab stop when the search input is shown', () => {
    const trigger = mount(Combobox, { props: { options, modelValue: null } }).get('[data-ui-combobox-trigger]');

    expect(trigger.attributes('role')).toBeUndefined();
    expect(trigger.attributes('aria-label')).toBeUndefined();
    expect(trigger.attributes('aria-haspopup')).toBeUndefined();
    expect(trigger.attributes('tabindex')).toBe('-1');
});
