import { afterEach, expect, test, vi } from 'vitest';
import shortcutLabel from '@/util/shortcutLabel.js';

afterEach(() => {
    vi.unstubAllGlobals();
});

test('mod is shown as Cmd on a Mac', () => {
    vi.stubGlobal('navigator', { platform: 'MacIntel' });

    expect(shortcutLabel('mod+shift+s')).toBe('Cmd+Shift+S');
});

test('mod is shown as Ctrl elsewhere', () => {
    vi.stubGlobal('navigator', { platform: 'Win32' });

    expect(shortcutLabel('mod+shift+s')).toBe('Ctrl+Shift+S');
});
