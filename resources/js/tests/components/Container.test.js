import { mount } from '@vue/test-utils';
import { afterEach, beforeEach, expect, test } from 'vitest';
import { defineComponent, h } from 'vue';
import * as Globals from '@/bootstrap/globals';
import Container from '@/components/ui/Publish/Container.vue';
import { useContentDirection } from '@/composables/content-direction';

Object.keys(Globals).forEach((fn) => (window[fn] = Globals[fn]));
window.__ = (key) => key;

window.Statamic = {
    $config: {
        get: (key) => (key === 'sites' ? [{ handle: 'default', direction: 'ltr' }] : undefined),
    },
    $dirty: { has: () => false, add: () => {}, remove: () => {} },
    $events: { $emit: () => {} },
};

const Probe = defineComponent({
    setup() {
        const { direction } = useContentDirection();
        return { direction };
    },
    render() {
        return h('div', { 'data-direction': this.direction });
    },
});

beforeEach(async () => {
    document.documentElement.setAttribute('dir', 'rtl');
    // Let the ui-direction composable's MutationObserver pick up the attribute change.
    await new Promise((resolve) => queueMicrotask(resolve));
});

afterEach(() => {
    document.documentElement.removeAttribute('dir');
});

test('content direction is unaffected by the CP/document direction when the site has an explicit direction', () => {
    const wrapper = mount(Container, {
        props: {
            blueprint: { tabs: [] },
            site: 'default',
        },
        slots: {
            default: () => h(Probe),
        },
    });

    expect(wrapper.find('[data-direction]').attributes('data-direction')).toBe('ltr');
});

test('content direction falls back to the UI direction when the site cannot be resolved', () => {
    const wrapper = mount(Container, {
        props: {
            blueprint: { tabs: [] },
            site: 'unknown-site',
        },
        slots: {
            default: () => h(Probe),
        },
    });

    expect(wrapper.find('[data-direction]').attributes('data-direction')).toBe('rtl');
});
