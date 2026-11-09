import { flushPromises, mount } from '@vue/test-utils';
import { afterEach, beforeEach, describe, expect, test, vi } from 'vitest';
import { h } from 'vue';
import { portals } from '@api';
import { Stack, StackClose } from '@/components/ui';

let wrapper;

async function openStack(props = {}, options = {}) {
    wrapper = mount(Stack, { props: { open: true, ...props }, ...options });

    const target = document.createElement('div');
    target.id = `portal-target-${portals.all()[portals.all().length - 1].id}`;
    document.body.appendChild(target);

    await flushPromises();

    return wrapper;
}

async function finishClosing() {
    vi.advanceTimersByTime(300);
    await flushPromises();
}

beforeEach(() => {
    vi.useFakeTimers();
});

afterEach(() => {
    wrapper?.unmount();
    wrapper = null;
    vi.useRealTimers();
    document.body.innerHTML = '';
});

const closeButtons = {
    'the header close button': {
        props: { title: 'Edit Page' },
        selector: '[data-ui-stack-title] button',
    },
    'the floating close button': {
        props: {},
        selector: '.stack-content > .fixed button',
    },
    'the StackClose component': {
        props: {},
        options: { slots: { default: () => h(StackClose, () => h('button', 'Cancel')) } },
        selector: '[data-ui-modal-close] button',
    },
};

describe.each(Object.entries(closeButtons))('%s', (_, { props, options, selector }) => {
    test('respects a beforeClose that returns false', async () => {
        const beforeClose = vi.fn(() => false);
        await openStack({ ...props, beforeClose }, options);

        document.querySelector(selector).click();
        await finishClosing();

        expect(beforeClose).toHaveBeenCalledOnce();
        expect(document.querySelector('.stack-content')).not.toBeNull();
        expect(wrapper.emitted('update:open')).toBeUndefined();
        expect(wrapper.emitted('closed')).toBeUndefined();
    });

    test('closes when beforeClose returns true', async () => {
        const beforeClose = vi.fn(() => true);
        await openStack({ ...props, beforeClose }, options);

        document.querySelector(selector).click();
        await finishClosing();

        expect(beforeClose).toHaveBeenCalledOnce();
        expect(document.querySelector('.stack-content')).toBeNull();
        expect(wrapper.emitted('update:open')).toEqual([[false]]);
        expect(wrapper.emitted('closed')).toHaveLength(1);
    });
});

test('closing programmatically skips beforeClose', async () => {
    const beforeClose = vi.fn(() => false);
    await openStack({ beforeClose });

    wrapper.vm.close();
    await finishClosing();

    expect(beforeClose).not.toHaveBeenCalled();
    expect(wrapper.emitted('update:open')).toEqual([[false]]);
});
