import { mount } from '@vue/test-utils';
import { expect, test } from 'vitest';
import { h } from 'vue';
import Badge from '@/components/ui/Badge.vue';

const mountBadge = (options) => mount(Badge, { global: { stubs: { Icon: true } }, ...options });

test('text from the text prop is wrapped so it can be trimmed', () => {
    const wrapper = mountBadge({ props: { text: 'Published' } });

    expect(wrapper.find('span.st-text-trim-optical-cap').text()).toBe('Published');
});

test('plain text in the slot is wrapped so it can be trimmed', () => {
    const wrapper = mountBadge({ slots: { default: 'Draft' } });

    expect(wrapper.find('span.st-text-trim-optical-cap').text()).toBe('Draft');
});

test('elements in the slot are rendered as they are', () => {
    const wrapper = mountBadge({ slots: { default: () => h('time', 'Yesterday') } });

    expect(wrapper.find('.st-text-trim-optical-cap').exists()).toBe(false);
    expect(wrapper.find('[data-ui-badge] > time').text()).toBe('Yesterday');
});

test('a mix of text and elements in the slot is rendered as it is', () => {
    const wrapper = mountBadge({ slots: { default: () => ['Edited ', h('time', 'yesterday')] } });

    expect(wrapper.find('.st-text-trim-optical-cap').exists()).toBe(false);
    expect(wrapper.find('[data-ui-badge]').text()).toBe('Edited yesterday');
});

test('prepended and appended text is wrapped so it can be trimmed', () => {
    const wrapper = mountBadge({ props: { prepend: 'Records', text: '1,204', append: 'cached' } });

    expect(wrapper.findAll('.st-text-trim-optical-cap').map((el) => el.text())).toEqual(['Records', '1,204', 'cached']);
});
