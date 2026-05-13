import { nextTick, onMounted, onUnmounted, watch } from 'vue';
import { Draggable, Sortable, Plugins } from '@shopify/draggable';

const DROP_ZONE_SELECTOR = '.fieldtype-source-container, .section-drop-zone, .section-gap-drop-zone';
const SORT_CONTAINER_SELECTOR = '.field-sort-container';

export function useDragAndDrop({ sections, onSectionAdded, onSectionAddedWithinSection, onFieldAdded, onFieldMoved }) {
    let draggable = null;
    let sortable = null;
    let lastClientY = 0;

    const dropTarget = createDropTarget();
    const dropIndicator = createDropIndicator();

    const refreshDraggable = () => {
        draggable?.destroy();
        initDraggable();
    };

    const refreshSortable = () => {
        sortable?.destroy();
        initSortable();
    };

    const initDraggable = () => {
        const containers = document.querySelectorAll(DROP_ZONE_SELECTOR);
        if (containers.length === 0) return;

        draggable = new Draggable(containers, {
            draggable: '.fieldtype-draggable',
            distance: 5,
            mirror: { constrainDimensions: true, appendTo: 'body' },
        });

        draggable.on('drag:start', () => dropTarget.reset());

        draggable.on('drag:move', (event) => {
            lastClientY = event.sensorEvent.clientY;
            renderIndicator(event);
        });

        draggable.on('drag:over:container', (event) => {
            if (event.overContainer.classList.contains('fieldtype-source-container')) {
                dropTarget.reset();
                hideIndicator();
                return;
            }
            if (isDropZone(event.overContainer)) dropTarget.enter(event.overContainer);
        });

        draggable.on('drag:out:container', (event) => dropTarget.leave(event.overContainer));

        draggable.on('drag:stop', (event) => {
            const target = dropTarget.effective;
            dropTarget.reset();
            hideIndicator();

            if (!target) return;
            handleDrop(target, event.source.dataset.fieldtype);
        });
    };

    const initSortable = () => {
        const containers = document.querySelectorAll(SORT_CONTAINER_SELECTOR);
        if (containers.length === 0) return;

        sortable = new Sortable(containers, {
            draggable: '[data-field-item]',
            handle: '[data-field-item]',
            distance: 5,
            mirror: { constrainDimensions: true, appendTo: 'body' },
            swapAnimation: { vertical: true },
            plugins: [Plugins.SwapAnimation],
            exclude: { plugins: [Draggable.Plugins.Focusable] },
        });

        sortable.on('drag:start', () => document.documentElement.classList.add('cursor-grabbing'));
        sortable.on('drag:stop', () => document.documentElement.classList.remove('cursor-grabbing'));

        sortable.on('sortable:stop', (event) => {
            const { oldIndex, newIndex, oldContainer, newContainer } = event;
            const from = oldContainer.dataset.sortSection;
            const to = newContainer.dataset.sortSection;

            if (!from || !to) return;
            if (from === to && oldIndex === newIndex) return;

            onFieldMoved(from, to, oldIndex, newIndex);
        });
    };

    const renderIndicator = (event) => {
        const target = dropTarget.effective;
        if (!target) return hideIndicator();

        const isGap = target.classList.contains('section-gap-drop-zone');
        const isSectionDrag = event.source.dataset.fieldtype === 'section';
        if (isGap && !isSectionDrag) return hideIndicator();

        if (isGap) return target.appendChild(dropIndicator);

        const sortContainer = target.querySelector(SORT_CONTAINER_SELECTOR);
        if (!sortContainer) return hideIndicator();

        const fields = sortContainer.querySelectorAll('[data-field-item]');
        if (fields.length === 0) {
            sortContainer.prepend(dropIndicator);
            return;
        }

        const index = indexFromClientY(fields, lastClientY);
        const reference = fields[index] ?? null;
        sortContainer.insertBefore(dropIndicator, reference);
    };

    const hideIndicator = () => dropIndicator.remove();

    const handleDrop = (target, fieldtypeHandle) => {
        if (!fieldtypeHandle) return;

        if (target.classList.contains('section-gap-drop-zone')) {
            if (fieldtypeHandle !== 'section') return;
            onSectionAdded(parseInt(target.dataset.sectionGapIndex, 10));
            return;
        }

        const sectionId = target.dataset.sectionDropZone;
        if (!sectionId) return;

        const fields = document.querySelectorAll(`[data-section-drop-zone="${sectionId}"] [data-field-item]`);
        const index = indexFromClientY(fields, lastClientY);

        if (fieldtypeHandle === 'section') {
            onSectionAddedWithinSection(sectionId, index);
        } else {
            onFieldAdded(sectionId, fieldtypeHandle, index);
        }
    };

    onMounted(async () => {
        await nextTick();
        await nextTick();
        initDraggable();
        initSortable();
    });

    onUnmounted(() => {
        draggable?.destroy();
        sortable?.destroy();
    });

    watch(() => sections.value.length, () => nextTick(refreshDraggable));

    watch(
        () => sections.value.map((s) => s.fields.length).join(','),
        () => nextTick(refreshSortable),
    );
}

function createDropTarget() {
    let current = null;
    let sticky = null;

    return {
        get effective() {
            return current ?? sticky;
        },
        enter(el) {
            current = el;
            sticky = el;
        },
        leave(el) {
            if (current === el) current = null;
        },
        reset() {
            current = null;
            sticky = null;
        },
    };
}

function createDropIndicator() {
    const el = document.createElement('div');
    el.className = 'h-1 w-full rounded-full bg-zinc-300 col-span-full';
    el.dataset.dropIndicator = '';
    return el;
}

function isDropZone(el) {
    return el.classList.contains('section-drop-zone') || el.classList.contains('section-gap-drop-zone');
}

function indexFromClientY(elements, clientY) {
    let index = 0;

    for (const el of elements) {
        const rect = el.getBoundingClientRect();
        if (clientY > rect.top + rect.height / 2) index++;
    }

    return index;
}

