import { flushPromises, mount } from '@vue/test-utils';
import { afterEach, expect, test } from 'vitest';
import { h, nextTick } from 'vue';
import { portals } from '@api';
import { Modal, ModalTitle, Button } from '@/components/ui';

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

    const content = document.querySelector('[data-ui-modal-content]');

    expect(document.activeElement).toBe(content);
    expect(content.getAttribute('tabindex')).toBe('-1');
});
