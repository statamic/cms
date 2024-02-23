import { closestVm } from '../bootstrap/globals';

class Reveal {

    element(element) {
        if (!element) return;
        let parent = element;
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
        Vue.nextTick(() => {
            element.scrollIntoView({
                block: 'center',
            });
        });
    }

    invalid() {
        Vue.nextTick(() => {
            const element = document.querySelector('.publish-field.has-error:not(:has(.has-error))');
            if (!element) return;
            this.element(element);
        });
    }
}

export default Reveal;
