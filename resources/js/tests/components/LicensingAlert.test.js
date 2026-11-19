import { flushPromises, mount } from '@vue/test-utils';
import { afterEach, beforeEach, expect, test, vi } from 'vitest';
import { nextTick } from 'vue';
import { portals } from '@api';
import LicensingAlert from '@/components/LicensingAlert.vue';

vi.mock('@inertiajs/vue3', () => ({
    router: { get: vi.fn() },
    usePage: () => ({
        props: {
            _statamic: {
                licensing: {
                    alert: {
                        message: 'Thanks for trying Statamic Pro!',
                        testing: true,
                        manageUrl: '/cp/utilities/licensing',
                    },
                },
            },
        },
    }),
    Link: { name: 'Link', template: '<a><slot /></a>' },
}));

beforeEach(() => {
    global.__ = (key) => key;
    localStorage.removeItem('statamic.snooze_license_banner');
});

afterEach(() => {
    document.body.innerHTML = '';
});

async function openAlert() {
    const wrapper = mount(LicensingAlert, { attachTo: document.body });

    const portal = portals.all()[portals.all().length - 1];
    const target = document.createElement('div');
    target.id = `portal-target-${portal.id}`;
    document.body.appendChild(target);

    await flushPromises();
    await nextTick();

    return wrapper;
}

test('opens with focus on the dialog, then tabbable actions', async () => {
    await openAlert();

    const dialog = document.querySelector('[data-ui-modal-content]');
    const buttons = [...dialog.querySelectorAll('button')];
    const snooze = buttons.find((button) => button.textContent.includes('Snooze'));
    const manage = buttons.find((button) => button.textContent.includes('Manage Licenses'));

    expect(document.activeElement).toBe(dialog);
    expect(dialog.getAttribute('aria-describedby')).toBeTruthy();
    expect(document.getElementById(dialog.getAttribute('aria-describedby')).textContent)
        .toContain('Thanks for trying Statamic Pro!');

    expect(snooze).toBeTruthy();
    expect(manage).toBeTruthy();
    expect(snooze.hasAttribute('tabindex')).toBe(false);
    expect(snooze.tabIndex).toBe(0);
    expect(manage.tabIndex).toBe(0);
});
