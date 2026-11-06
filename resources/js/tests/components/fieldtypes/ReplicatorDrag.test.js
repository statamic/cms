import { expect, test, vi } from 'vitest';
import Replicator from '@/components/fieldtypes/replicator/Replicator.vue';

// The drag itself needs a real sortable list, so these exercise the handlers directly.
function dragging() {
    const sets = document.createElement('section');

    return {
        sets,
        vm: {
            $refs: { sets },
            $emit: vi.fn(),
            collapseAll: vi.fn(),
            dragging: false,
        },
    };
}

test('dragging hides the set bodies without collapsing the sets', () => {
    const { sets, vm } = dragging();

    Replicator.methods.dragStarted.call(vm, {});

    expect(sets.classList.contains('replicator-dragging')).toBe(true);
    expect(vm.dragging).toBe(true);
    expect(vm.collapseAll).not.toHaveBeenCalled();
});

test('the set bodies are shown again when the drag ends', () => {
    const { sets, vm } = dragging();

    Replicator.methods.dragStarted.call(vm, {});
    Replicator.methods.dragEnded.call(vm);

    expect(sets.classList.contains('replicator-dragging')).toBe(false);
    expect(vm.dragging).toBe(false);
});
