import { nextTick } from 'vue'; // Import nextTick from Vue 3
import { closestVm } from '../bootstrap/globals';

class Reveal {
    element(el) {
        if (!el) return;
        let parent = el;
        while (parent) {
            if (parent.matches('.tab-panel')) {
                closestVm(parent, 'publish-tabs').setActive(parent.dataset.tabHandle);
            }
            if (parent.matches('.replicator-set')) {
                closestVm(parent, 'replicator-fieldtype-set').expand();
            }
            if (parent.matches('.bard-set')) {
                closestVm(parent, 'bard-fieldtype-set').expand();
            }
            parent = parent.parentElement;
        }
        nextTick(() => { // Use the imported nextTick function
            el.scrollIntoView({
                block: 'center',
            });
        });
    }

    invalid() {
        nextTick(() => { // Use the imported nextTick function
            const el = document.querySelector('.publish-field.has-error:not(:has(.publish-field.has-error))');
            if (!el) return;
            this.element(el);
        });
    }
}

export default Reveal;
