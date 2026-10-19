import { expect, test } from 'vitest';
import Portals from '@/components/portals/Portals.js';

test('top is the last portal created', () => {
    const portals = new Portals();
    portals.create('modal');
    const stack = portals.create('stack');

    expect(portals.top().id).toBe(stack.id);
});

test('top is undefined when there are no portals', () => {
    expect(new Portals().top()).toBeUndefined();
});

test('top skips disabled portals', () => {
    const portals = new Portals();
    const modal = portals.create('modal');
    const fullscreen = portals.create('bard-fullscreen');

    fullscreen.setDisabled(true);
    expect(portals.top().id).toBe(modal.id);

    fullscreen.setDisabled(false);
    expect(portals.top().id).toBe(fullscreen.id);
});

test('destroying a portal forgets that it was disabled', () => {
    const portals = new Portals();
    const fullscreen = portals.create('bard-fullscreen');

    fullscreen.setDisabled(true);
    fullscreen.destroy();

    expect(portals.disabled.has(fullscreen.id)).toBe(false);
});
