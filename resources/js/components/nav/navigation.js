import { ref } from 'vue';
import useStatamicPageProps from '@/composables/page-props.js';
import { deepClone } from '@/util/clone.js';

let nav = null;

export default function useNavigation() {
    if (!nav) {
        const { nav: navProp } = useStatamicPageProps();
        nav = ref(deepClone(navProp));
    }

    function unsetActiveItem() {
        nav.value.forEach(section => {
            section.items.forEach(item => {
                item.active = false;
                item.children.forEach(child => child.active = false);
            });
        });
    }

    function setParentActive(parent) {
        unsetActiveItem();
        parent.active = true;
    }

    function setChildActive(parent, child) {
        setParentActive(parent);
        child.active = true;
    }

    return {
        nav, setParentActive, setChildActive
    }
}
