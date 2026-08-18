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

function gapRect(trigger, popper) {
    const left = Math.min(trigger.left, popper.left);
    const right = Math.max(trigger.right, popper.right);

    if (popper.bottom <= trigger.top) return { left, right, top: popper.bottom, bottom: trigger.top };
    if (popper.top >= trigger.bottom) return { left, right, top: trigger.bottom, bottom: popper.top };

    return null;
}

function isPointerInside(x, y) {
    const trigger = triggerRect();
    const popper = popperRect();

    if (contains(trigger, x, y) || contains(popper, x, y)) return true;

    return !!trigger && !!popper && contains(gapRect(trigger, popper), x, y, 0);
}

function onPointerMove(event) {
    pointer = { x: event.clientX, y: event.clientY };

    if (!isPointerInside(pointer.x, pointer.y)) dismiss();
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
        if (pointer && !isPointerInside(pointer.x, pointer.y)) dismiss();
        return;
    }

    dismiss();
}

function dismissFor(el) {
    if (targetEl.value === el || pendingEl === el) dismiss();
}

function registerContentEl(el) {
    contentEl.value = el ?? null;
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
