import { flushPromises, mount } from '@vue/test-utils';
import { afterEach, beforeEach, describe, expect, test, vi } from 'vitest';
import { h, nextTick } from 'vue';
import { portals } from '@api';
import { Modal, ModalClose, ModalTitle, Button } from '@/components/ui';

async function openModal(props = {}, options = {}) {
    const wrapper = mount(Modal, { props: { open: true, ...props }, ...options });

    const target = document.createElement('div');
    target.id = `portal-target-${portals.all()[portals.all().length - 1].id}`;
    document.body.appendChild(target);

    await flushPromises();

    return wrapper;
}

afterEach(() => {
    document.body.innerHTML = '';
});

test('content is a dialog labelled by its title', async () => {
    await openModal({ title: 'Delete Entry' });

    const content = document.querySelector('[data-ui-modal-content]');

    expect(content.getAttribute('role')).toBe('dialog');
    expect(content.getAttribute('aria-modal')).toBe('true');
    expect(document.getElementById(content.getAttribute('aria-labelledby')).textContent).toContain('Delete Entry');
});

test('content is labelled by the modal title component', async () => {
    await openModal({}, { slots: { default: h(ModalTitle, () => 'Remove Page') } });

    const content = document.querySelector('[data-ui-modal-content]');

    expect(document.getElementById(content.getAttribute('aria-labelledby')).textContent).toContain('Remove Page');
});

test('content has no accessible name when there is no title', async () => {
    await openModal();

    const content = document.querySelector('[data-ui-modal-content]');

    expect(content.hasAttribute('aria-labelledby')).toBe(false);
});

test('autofocuses the first focusable element by default', async () => {
    await openModal({ title: 'Delete Entry' }, {
        slots: { footer: () => h(Button, { text: 'Confirm' }) },
    });
    await nextTick();

    expect(document.activeElement?.textContent).toContain('Confirm');
});

test('focuses the dialog when autofocus is disabled', async () => {
    await openModal({ title: 'Delete Entry', autofocus: false }, {
        slots: { footer: () => h(Button, { text: 'Confirm' }) },
    });
    await nextTick();

    expect(document.activeElement).toBe(document.querySelector('[data-ui-modal-content]'));
});

test('hides the page behind from the accessibility tree', async () => {
    vi.useFakeTimers();

    const page = document.createElement('main');
    page.textContent = 'Behind the modal';
    document.body.appendChild(page);

    const wrapper = await openModal({ title: 'Delete Entry' });

    expect(page.getAttribute('aria-hidden')).toBe('true');

    await wrapper.setProps({ open: false });
    await vi.advanceTimersByTimeAsync(300);
    await flushPromises();

    expect(page.hasAttribute('aria-hidden')).toBe(false);

    vi.useRealTimers();
});

test('keeps focus trapped when a disabled portal is created after it', async () => {
    await openModal({ title: 'Licensing Alert', autofocus: false });
    await nextTick();

    // e.g. a Bard field's fullscreen portal, registered as the page behind loads
    const fullscreen = portals.create('bard-fullscreen');
    fullscreen.setDisabled(true);
    await nextTick();

    const input = document.createElement('input');
    document.body.appendChild(input);
    input.focus();

    expect(document.activeElement).toBe(document.querySelector('[data-ui-modal-content]'));

    fullscreen.destroy();
});

describe('ModalClose', () => {
    const closeButton = { slots: { default: () => h(ModalClose, () => h('button', 'Cancel')) } };

    beforeEach(() => {
        vi.useFakeTimers();
    });

    afterEach(() => {
        vi.useRealTimers();
    });

    async function clickClose(props) {
        const wrapper = await openModal(props, closeButton);

        document.querySelector('[data-ui-modal-close] button').click();
        await vi.advanceTimersByTimeAsync(300);
        await flushPromises();

        return wrapper;
    }

    test('respects a beforeClose that returns false', async () => {
        const beforeClose = vi.fn(() => false);
        const wrapper = await clickClose({ beforeClose });

        expect(beforeClose).toHaveBeenCalledOnce();
        expect(document.querySelector('[data-ui-modal-content]')).not.toBeNull();
        expect(wrapper.emitted('update:open')).toBeUndefined();

        wrapper.unmount();
    });

    test('closes when beforeClose returns true', async () => {
        const beforeClose = vi.fn(() => true);
        const wrapper = await clickClose({ beforeClose });

        expect(beforeClose).toHaveBeenCalledOnce();
        expect(document.querySelector('[data-ui-modal-content]')).toBeNull();
        expect(wrapper.emitted('update:open')).toEqual([[false]]);

        wrapper.unmount();
    });

    test('closes when the modal is not dismissible', async () => {
        const wrapper = await clickClose({ dismissible: false });

        expect(document.querySelector('[data-ui-modal-content]')).toBeNull();
        expect(wrapper.emitted('update:open')).toEqual([[false]]);

        wrapper.unmount();
    });
});
