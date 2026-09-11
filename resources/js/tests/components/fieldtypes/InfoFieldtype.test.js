import { mount } from '@vue/test-utils';
import { describe, expect, test } from 'vitest';
import InfoFieldtype from '@/components/fieldtypes/InfoFieldtype.vue';
import { Icon } from '@/components/ui';

window.__ = (key) => key;

function mountField(config = {}) {
    return mount(InfoFieldtype, {
        props: {
            value: null,
            handle: 'info',
            config,
        },
    });
}

describe.each([
    ['notice', 'default'],
    ['tip', 'tip'],
    ['warning', 'warning'],
    ['important', 'error'],
    ['success', 'success'],
])('%s state', (state, variant) => {
    test(`uses the ${variant} alert variant`, () => {
        const wrapper = mountField({ state, content: 'Something happened.' });

        expect(wrapper.get('[data-ui-alert]').attributes('data-variant')).toBe(variant);
    });
});

test('renders sanitized markdown with links and lists', () => {
    const wrapper = mountField({
        content: '- First item\n- [Statamic](https://statamic.com)\n\n<script>alert("nope")</script>',
    });

    expect(wrapper.findAll('li')).toHaveLength(2);
    expect(wrapper.get('a').attributes()).toMatchObject({
        href: 'https://statamic.com',
        target: '_blank',
    });
    expect(wrapper.find('script').exists()).toBe(false);
});

test('uses a configured icon', () => {
    const wrapper = mountField({ content: 'Hello', icon: 'lightbulb-idea' });

    expect(wrapper.findComponent(Icon).props('name')).toBe('lightbulb-idea');
});
