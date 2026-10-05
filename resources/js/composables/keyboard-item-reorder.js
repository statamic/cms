import { computed, nextTick, onBeforeUnmount, ref, toValue, watch } from 'vue';

/**
 * Menu-initiated keyboard reordering: select "Move", then ArrowUp/ArrowDown to
 * reorder, Enter/Escape to finish.
 *
 * Keys are handled on window (capture) so this still works if the dropdown steals
 * focus back to its trigger after closing.
 */
export function useKeyboardItemReorder({ index, total, onMove }) {
    const moving = ref(false);
    const rootEl = ref(null);
    const status = ref('');
    let startTimer = null;

    const canMoveUp = computed(() => toValue(index) > 0);
    const canMoveDown = computed(() => toValue(index) < toValue(total) - 1);

    function announcePosition() {
        status.value = __('Item :current of :total', {
            current: toValue(index) + 1,
            total: toValue(total),
        });
    }

    function focusRoot() {
        nextTick(() => {
            rootEl.value?.focus({ preventScroll: true });
        });
    }

    function onKeydown(event) {
        if (!moving.value) return;

        if (event.key === 'ArrowUp') {
            event.preventDefault();
            event.stopPropagation();
            if (canMoveUp.value) {
                onMove(toValue(index), toValue(index) - 1);
            }
            return;
        }

        if (event.key === 'ArrowDown') {
            event.preventDefault();
            event.stopPropagation();
            if (canMoveDown.value) {
                onMove(toValue(index), toValue(index) + 1);
            }
            return;
        }

        if (event.key === 'Enter' || event.key === 'Escape') {
            event.preventDefault();
            event.stopPropagation();
            stopMoving({ restoreFocus: true });
        }
    }

    function onPointerDown(event) {
        if (!moving.value) return;
        // rootEl is only the focus sentinel — treat the whole moving item as inside.
        if (event.target.closest?.('[data-moving]')) return;
        stopMoving();
    }

    function bindListeners() {
        window.addEventListener('keydown', onKeydown, true);
        window.addEventListener('pointerdown', onPointerDown, true);
    }

    function unbindListeners() {
        window.removeEventListener('keydown', onKeydown, true);
        window.removeEventListener('pointerdown', onPointerDown, true);
    }

    function startMoving() {
        clearTimeout(startTimer);

        // Wait for the dropdown to finish closing / restoring focus, otherwise
        // our focus and the first keypress get eaten by the menu teardown.
        startTimer = setTimeout(() => {
            moving.value = true;
            status.value = __('Use up and down arrows to reorder. Press Enter or Escape when finished.');
            bindListeners();
            focusRoot();
        }, 50);
    }

    function stopMoving({ restoreFocus = false } = {}) {
        clearTimeout(startTimer);
        startTimer = null;

        if (!moving.value) return;

        const trigger = rootEl.value?.parentElement?.querySelector('[data-ui-dropdown-trigger]');
        moving.value = false;
        status.value = '';
        unbindListeners();

        if (restoreFocus) {
            nextTick(() => trigger?.focus({ preventScroll: true }));
        }
    }

    watch(
        () => toValue(index),
        () => {
            if (!moving.value) return;
            announcePosition();
            focusRoot();
        },
    );

    onBeforeUnmount(() => {
        clearTimeout(startTimer);
        unbindListeners();
    });

    return {
        moving,
        rootEl,
        status,
        canMoveUp,
        canMoveDown,
        startMoving,
        stopMoving,
    };
}
