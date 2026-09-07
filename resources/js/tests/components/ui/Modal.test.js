import { mount } from '@vue/test-utils';
import { expect, test } from 'vitest';
import { h, nextTick } from 'vue';
import { portals } from '@api';
import { Modal, ModalTitle } from '@/components/ui';

async function openModal(options = {}) {
    const wrapper = mount(Modal, { props: { open: true }, ...options });

    const target = document.createElement('div');
    target.id = `portal-target-${portals.all()[portals.all().length - 1].id}`;
    document.body.appendChild(target);

    await nextTick();
    await nextTick();
    await nextTick();

    return wrapper;
}

test('content is a dialog labelled by its title', async () => {
    await openModal({ props: { open: true, title: 'Delete Entry' } });

    const content = document.querySelector('[data-ui-modal-content]');

    expect(content.getAttribute('role')).toBe('dialog');
    expect(content.getAttribute('aria-modal')).toBe('true');
    expect(document.getElementById(content.getAttribute('aria-labelledby')).textContent).toContain('Delete Entry');
});

test('content is labelled by the modal title component', async () => {
    await openModal({
        slots: { default: h(ModalTitle, () => 'Delete Entry') },
    });

    const content = document.querySelector('[data-ui-modal-content]');

    expect(document.getElementById(content.getAttribute('aria-labelledby')).textContent).toContain('Delete Entry');
});
