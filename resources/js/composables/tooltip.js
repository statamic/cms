import { ref, shallowRef, readonly } from 'vue';

const isVisible = ref(false);
const content = ref('');
const html = ref(false);
const copyable = ref(false);
const targetEl = shallowRef(null);
const contentEl = shallowRef(null);

let showTimeout = null;
let pendingEl = null;
let tracking = false;
let pointer = null;

const EDGE = 4;

function isInteractive() {
    return html.value || copyable.value;
}

function popperRect() {
    const el = contentEl.value;
    if (!el?.isConnected) return null;

    return (el.closest('.v-popper__popper') ?? el).getBoundingClientRect();
}

function triggerRect() {
    const el = targetEl.value;

    return el?.isConnected ? el.getBoundingClientRect() : null;
}

function contains(rect, x, y, pad = EDGE) {
    return (
        !!rect &&
        x >= rect.left - pad &&
        x <= rect.right + pad &&
        y >= rect.top - pad &&
        y <= rect.bottom + pad
    );
}

export function tooltipGapRect(trigger, popper) {
    if (!trigger || !popper) return null;

    if (popper.bottom <= trigger.top) {
        return {
            left: Math.min(trigger.left, popper.left),
            right: Math.max(trigger.right, popper.right),
            top: popper.bottom,
            bottom: trigger.top,
        };
    }

    if (popper.top >= trigger.bottom) {
        return {
            left: Math.min(trigger.left, popper.left),
            right: Math.max(trigger.right, popper.right),
            top: trigger.bottom,
            bottom: popper.top,
        };
    }

    if (popper.right <= trigger.left) {
        return {
            left: popper.right,
            right: trigger.left,
            top: Math.min(trigger.top, popper.top),
            bottom: Math.max(trigger.bottom, popper.bottom),
        };
    }

    if (popper.left >= trigger.right) {
        return {
            left: trigger.right,
            right: popper.left,
            top: Math.min(trigger.top, popper.top),
            bottom: Math.max(trigger.bottom, popper.bottom),
        };
    }

    return null;
}

export function isInTooltipHoverRegion(x, y, trigger, popper, edge = EDGE) {
    if (contains(trigger, x, y, edge) || contains(popper, x, y, edge)) return true;

    // Popper isn't measured yet — keep the tooltip so the pointer can cross the gap.
    if (trigger && !popper) return true;

    return !!trigger && !!popper && contains(tooltipGapRect(trigger, popper), x, y, 0);
}

function isOverInteractiveTooltip() {
    if (pointer) {
        return isInTooltipHoverRegion(pointer.x, pointer.y, triggerRect(), popperRect());
    }

    if (targetEl.value?.matches?.(':hover')) return true;
    if (contentEl.value?.matches?.(':hover')) return true;
    if (contentEl.value?.closest?.('.v-popper__popper')?.matches?.(':hover')) return true;

    // Still mounting, and we have not seen a pointer yet.
    return isVisible.value && isInteractive() && !popperRect();
}

function onPointerMove(event) {
    pointer = { x: event.clientX, y: event.clientY };

    if (!isOverInteractiveTooltip()) dismiss();
}

function startTracking() {
    if (tracking) return;
    tracking = true;
    document.addEventListener('mousemove', onPointerMove, true);
    document.addEventListener('mouseleave', dismiss);
    window.addEventListener('blur', dismiss);
}

function stopTracking() {
    if (!tracking) return;
    tracking = false;
    pointer = null;
    document.removeEventListener('mousemove', onPointerMove, true);
    document.removeEventListener('mouseleave', dismiss);
    window.removeEventListener('blur', dismiss);
}

function setContent(el, options) {
    targetEl.value = el;

    if (typeof options === 'string') {
        content.value = options;
        html.value = false;
        copyable.value = false;
    } else if (options && typeof options === 'object') {
        content.value = options.content || '';
        html.value = options.html || false;
        copyable.value = options.copyable || false;
    } else {
        content.value = '';
        html.value = false;
        copyable.value = false;
    }

    isInteractive() ? startTracking() : stopTracking();
}

function dismiss() {
    stopTracking();

    if (showTimeout) {
        clearTimeout(showTimeout);
        showTimeout = null;
    }

    pendingEl = null;

    isVisible.value = false;
    targetEl.value = null;
    contentEl.value = null;
    content.value = '';
    html.value = false;
    copyable.value = false;
}

function show(el, options) {
    if (showTimeout) {
        clearTimeout(showTimeout);
    }

    // If already visible, update immediately (for moving between adjacent elements)
    if (isVisible.value) {
        setContent(el, options);
        return;
    }

    pendingEl = el;

    showTimeout = setTimeout(() => {
        pendingEl = null;
        setContent(el, options);

        if (content.value) {
            isVisible.value = true;
        }
    }, 200);
}

function hide() {
    if (isVisible.value && isInteractive()) {
        if (isOverInteractiveTooltip()) return;
    }

    dismiss();
}

function dismissFor(el) {
    if (targetEl.value === el || pendingEl === el) dismiss();
}

function registerContentEl(el) {
    contentEl.value = el ?? null;

    if (el && isVisible.value && isInteractive() && !isOverInteractiveTooltip()) {
        dismiss();
    }
}

export function useTooltip() {
    return {
        isVisible: readonly(isVisible),
        content: readonly(content),
        html: readonly(html),
        copyable: readonly(copyable),
        targetEl: readonly(targetEl),
        show,
        hide,
        dismissFor,
        registerContentEl,
    };
}
