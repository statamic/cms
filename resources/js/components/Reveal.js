import { nextTick, onMounted, onBeforeUnmount } from 'vue';

const registry = new WeakMap();

const FOCUSABLE_CONTROL_SELECTORS = [
    'input:not([disabled]):not([type="hidden"]):not([aria-disabled="true"])',
    'textarea:not([disabled]):not([aria-disabled="true"])',
    'select:not([disabled]):not([aria-disabled="true"])',
    '[role="switch"]:not([disabled]):not([aria-disabled="true"]):not([data-disabled])',
    '[role="combobox"]:not([disabled]):not([aria-disabled="true"]):not([data-disabled])',
    '[role="slider"]:not([disabled]):not([aria-disabled="true"])',
    'button[data-ui-control]:not([disabled])',
    'button:not([disabled]):not([aria-disabled="true"]):not([data-disabled])',
];

class Reveal {
    use(ref, callback) {
        onMounted(() => this.mount(ref.value, callback));
    }

    mount(el, callback) {
        // Progressive set mounting can call this before the template ref exists.
        if (!el) return;

        registry.set(el, callback);

        onBeforeUnmount(() => registry.delete(el));
    }

    element(el) {
        let parent = el;

        while (parent) {
            const callback = registry.get(parent);
            if (callback) callback(parent);
            parent = parent.parentElement;
        }

        nextTick(() => {
            el.scrollIntoView({
                block: 'center',
            });
        });
    }

    focusableControl(root) {
        if (!root) return null;

        for (const selector of FOCUSABLE_CONTROL_SELECTORS) {
            for (const control of root.querySelectorAll(selector)) {
                if (control.closest('[data-ui-field-header]')) continue;
                if (!this.isVisible(control)) continue;
                if (control.tabIndex < 0 && control.getAttribute('role') === 'combobox') continue;
                return control;
            }
        }

        return null;
    }

    isVisible(control) {
        if (control.closest('[hidden], [inert]')) return false;

        const visibility = getComputedStyle(control).visibility;
        if (visibility === 'hidden' || visibility === 'collapse') return false;

        let element = control;
        while (element) {
            if (getComputedStyle(element).display === 'none') return false;
            element = element.parentElement;
        }

        return true;
    }

    invalid() {
        nextTick(() => {
            const el = document.querySelector('[data-ui-field-has-errors]:not(:has([data-ui-field-has-errors]))');
            if (!el) return;

            const fieldId = el.id || el.querySelector('[data-ui-label]')?.htmlFor || null;
            this.element(el);

            nextTick(() => {
                const current =
                    document.querySelector('[data-ui-field-has-errors]:not(:has([data-ui-field-has-errors]))') || el;
                const fallback = fieldId ? document.getElementById(fieldId) : null;
                const control =
                    this.focusableControl(current) ||
                    (fallback && this.isVisible(fallback) ? fallback : null);

                control?.focus?.({ preventScroll: true });
            });
        });
    }
}

export default Reveal;
