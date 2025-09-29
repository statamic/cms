import { ref, watch } from 'vue';

export default function useNavigation() {
    const nav = ref(Statamic.$config.get('nav'));

    watch(
        nav,
        (newNav) => Statamic.$config.set('nav', newNav),
        { deep: true }
    );

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
