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
];

class Reveal {
    use(ref, callback) {
        onMounted(() => this.mount(ref.value, callback));
    }

    mount(el, callback) {
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
            const control = root.querySelector(selector);
            if (!control) continue;
            if (control.tabIndex < 0 && control.getAttribute('role') === 'combobox') continue;
            return control;
        }

        return null;
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
                const control =
                    this.focusableControl(current) ||
                    (fieldId ? document.getElementById(fieldId) : null);

                control?.focus?.({ preventScroll: true });
            });
        });
    }
}

export default Reveal;
