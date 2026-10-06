import { computed, nextTick, onBeforeUnmount, ref, toValue, watch } from 'vue';

/**
 * Keyboard reordering: activate from the drag handle or Move menu, then
 * ArrowUp/ArrowDown to reorder, Enter/Escape to finish.
 *
 * Keys are handled on window (capture) so this still works if the dropdown steals
 * focus back to its trigger after closing.
 */
export function useKeyboardItemReorder({ index, total, onMove }) {
    const moving = ref(false);
    const moveOrigin = ref('end');
    const rootEl = ref(null);
    const status = ref('');
    let startTimer = null;

    const canMoveUp = computed(() => toValue(index) > 0);
    const canMoveDown = computed(() => toValue(index) < toValue(total) - 1);

    function announcePosition() {
        status.value = __('messages.keyboard_item_reorder_position', {
            current: toValue(index) + 1,
            total: toValue(total),
        });
    }

    function movingItem() {
        return rootEl.value?.closest('[data-moving]');
    }

    function dropdownTrigger() {
        return movingItem()?.querySelector('[data-ui-dropdown-trigger]')
            ?? rootEl.value?.parentElement?.querySelector('[data-ui-dropdown-trigger]');
    }

    function focusRoot() {
        nextTick(() => {
            // Dropdown close restores focus to the ⋯ trigger — take it back.
            dropdownTrigger()?.blur();
            rootEl.value?.focus({ preventScroll: true });
        });
    }

    function scrollMovingItemIntoView() {
        nextTick(() => {
            movingItem()?.scrollIntoView({ block: 'center', inline: 'nearest' });
            focusRoot();
        });
    }

    function onKeydown(event) {
        if (!moving.value) return;

        if (event.key === 'ArrowUp') {
            event.preventDefault();
            event.stopPropagation();
            if (canMoveUp.value) {
                onMove(toValue(index), toValue(index) - 1);
            } else {
                announcePosition();
            }
            return;
        }

        if (event.key === 'ArrowDown') {
            event.preventDefault();
            event.stopPropagation();
            if (canMoveDown.value) {
                onMove(toValue(index), toValue(index) + 1);
            } else {
                announcePosition();
            }
            return;
        }

        if (event.key === 'Enter' || event.key === 'Escape') {
            event.preventDefault();
            event.stopPropagation();
            stopMoving({ restoreFocus: true });
            return;
        }

        // Leave move mode when tabbing away so we don't trap page-wide keys.
        if (event.key === 'Tab') {
            stopMoving();
        }
    }

    function onPointerDown(event) {
        if (!moving.value) return;
        // rootEl is only the focus sentinel — treat the whole moving item as inside.
        if (event.target.closest?.('[data-moving]')) return;
        stopMoving();
    }

    function onFocusIn(event) {
        if (!moving.value) return;

        // Dropdown close restores focus to the ⋯ trigger — take it back.
        if (event.target?.closest?.('[data-ui-dropdown-trigger]')) {
            focusRoot();
            return;
        }

        // Focus left the item (e.g. Shift+Tab into a field) — end move mode.
        if (!event.target?.closest?.('[data-moving]')) {
            stopMoving();
        }
    }

    function bindListeners() {
        window.addEventListener('keydown', onKeydown, true);
        window.addEventListener('pointerdown', onPointerDown, true);
        window.addEventListener('focusin', onFocusIn, true);
    }

    function unbindListeners() {
        window.removeEventListener('keydown', onKeydown, true);
        window.removeEventListener('pointerdown', onPointerDown, true);
        window.removeEventListener('focusin', onFocusIn, true);
    }

    function startMoving(origin = 'end') {
        clearTimeout(startTimer);
        // Ignore non-string args (e.g. click/keyboard events bound directly).
        moveOrigin.value = origin === 'start' ? 'start' : 'end';

        if (toValue(total) < 2) return;

        // Wait for the dropdown to finish closing / restoring focus, otherwise
        // our focus and the first keypress get eaten by the menu teardown.
        startTimer = setTimeout(() => {
            moving.value = true;
            status.value = __('messages.keyboard_item_reorder_instructions');
            bindListeners();
            focusRoot();
            // Catch late focus restoration from the menu after our first focus attempt.
            requestAnimationFrame(() => focusRoot());
        }, 50);
    }

    function stopMoving({ restoreFocus = false } = {}) {
        clearTimeout(startTimer);
        startTimer = null;

        if (!moving.value) return;

        // Land on the whole row/set (not the grab handle or ⋯ menu).
        const item = movingItem();
        const focusTarget = item?.querySelector('[data-reorder-focus]') ?? item;

        moving.value = false;
        status.value = '';
        unbindListeners();

        if (restoreFocus) {
            nextTick(() => {
                focusTarget?.focus({ preventScroll: true, focusVisible: true });
            });
        }
    }

    watch(
        () => toValue(index),
        () => {
            if (!moving.value) return;
            announcePosition();
            scrollMovingItemIntoView();
        },
    );

    onBeforeUnmount(() => {
        clearTimeout(startTimer);
        unbindListeners();
    });

    return {
        moving,
        moveOrigin,
        rootEl,
        status,
        canMoveUp,
        canMoveDown,
        startMoving,
        stopMoving,
    };
}
