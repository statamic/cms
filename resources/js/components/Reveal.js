import { nextTick, onMounted, onBeforeUnmount } from 'vue';

const registry = new WeakMap();

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

    invalid() {
        nextTick(() => {
            const el = document.querySelector('[data-ui-field-has-errors]:not(:has([data-ui-field-has-errors]))');
            if (!el) return;
            this.element(el);
        });
    }
}

export default Reveal;
