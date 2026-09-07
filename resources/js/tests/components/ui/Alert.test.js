import { mount } from '@vue/test-utils';
import { expect, test } from 'vitest';
import { Alert, Icon } from '@/components/ui';

test('tip variant is blue and uses the tip icon', () => {
    const wrapper = mount(Alert, {
        props: {
            text: 'This is a helpful tip',
            variant: 'tip',
        },
    });

    const alert = wrapper.get('[data-ui-alert]');

    expect(alert.classes()).toEqual(expect.arrayContaining([
        'bg-blue-50',
        'border-blue-200',
        'text-blue-800',
    ]));
    expect(alert.attributes()).toMatchObject({
        role: 'status',
        'aria-live': 'polite',
        'data-variant': 'tip',
    });
    expect(wrapper.findComponent(Icon).props('name')).toBe('lightbulb-idea');
});
