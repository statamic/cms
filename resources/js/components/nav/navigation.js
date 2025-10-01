import { ref } from 'vue';
import useStatamicPageProps from '@/composables/page-props.js';
import { deepClone } from '@/util/clone.js';
import { router } from '@inertiajs/vue3';

let navData = null;
let breadcrumbsData = null;

function unsetActiveItem(data) {
    data.forEach(section => {
        section.items.forEach(item => {
            item.active = false;
            item.children.forEach(child => child.active = false);
        });
    });
}

function findItemInNav(data, id) {
    for (const section of data) {
        for (const item of section.items) {
            if (item.id === id) {
                return item;
            }
        }
    }
}

function findItemInChildren(parent, id) {
    return parent.children.find(c => c.id === id);
}

function applyUpdate(data, update) {
    unsetActiveItem(data);

    if (update.type === 'parent') {
        findItemInNav(data, update.parentId).active = true;
    } else if (update.type === 'child') {
        const parent = findItemInNav(data, update.parentId);
        parent.active = true;
        findItemInChildren(parent, update.childId).active = true;
    }
}

router.on('success', () => {
    const freshNav = deepClone(useStatamicPageProps().nav);
    navData.value = freshNav;
    breadcrumbsData.value = deepClone(freshNav);
});

export default function useNavigation() {
    if (!navData) {
        const { nav: navProp } = useStatamicPageProps();
        const cloned = deepClone(navProp);
        navData = ref(cloned);
        breadcrumbsData = ref(deepClone(cloned));
    }

    function setParentActive(parent, source = 'nav') {
        const update = { type: 'parent', parentId: parent.id };

        if (source === 'nav') {
            // Nav: update immediately, breadcrumbs wait for success
            applyUpdate(navData.value, update);
        } else {
            // Breadcrumbs: update both immediately
            applyUpdate(navData.value, update);
            applyUpdate(breadcrumbsData.value, update);
        }
    }

    function setChildActive(parent, child, source = 'nav') {
        const update = { type: 'child', parentId: parent.id, childId: child.id };

        if (source === 'nav') {
            // Nav: update immediately, breadcrumbs wait for success
            applyUpdate(navData.value, update);
        } else {
            // Breadcrumbs: update both immediately
            applyUpdate(navData.value, update);
            applyUpdate(breadcrumbsData.value, update);
        }
    }

    return {
        nav: navData,
        breadcrumbs: breadcrumbsData,
        setParentActive,
        setChildActive
    }
}
