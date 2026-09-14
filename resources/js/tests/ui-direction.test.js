import { test, expect, beforeEach, vi } from 'vitest';

let useUiDirection;

beforeEach(async () => {
    vi.resetModules();
    document.documentElement.removeAttribute('dir');
    useUiDirection = (await import('../composables/ui-direction.js')).useUiDirection;
});

test('defaults to ltr when html dir is missing', () => {
    const { direction } = useUiDirection();

    expect(direction.value).toBe('ltr');
});

test('updates reactively when the html dir attribute changes', async () => {
    const { direction } = useUiDirection();
    expect(direction.value).toBe('ltr');

    document.documentElement.setAttribute('dir', 'rtl');

    await vi.waitFor(() => expect(direction.value).toBe('rtl'));
});
