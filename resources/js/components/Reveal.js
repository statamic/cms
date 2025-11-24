import { closestVm } from '../bootstrap/globals';
import { nextTick } from 'vue';

class Reveal {
    element(el) {
        if (!el) return;
        let parent = el;
        while (parent) {
            if (parent.matches('[data-publish-tab]')) {
                let rekaTabsPrimitive = closestVm(parent, 'Tabs');
                let publishTabsComponent = closestVm(rekaTabsPrimitive.parent.vnode.el, 'Tabs');

                publishTabsComponent?.exposed.setActive(parent.dataset.publishTab);
            }
            if (parent.matches('[data-replicator-set]')) {
                closestVm(parent, 'Set').exposed.expand();
            }
            if (parent.matches('[data-bard-set]')) {
                closestVm(parent, 'BardSet').ctx.expand();
            }
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
            const el = document.querySelector('[data-ui-field-has-errors="true"]:not(:has([data-ui-field-has-errors="true"]))');
            if (!el) return;
            this.element(el);
        });
    }
}

export default Reveal;
