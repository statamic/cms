import { nextTick, onMounted, onUnmounted, watch } from 'vue';
import { Draggable, Sortable } from '@shopify/draggable';

const SORT_CONTAINER_SELECTOR = '.field-sort-container';

/**
 * Handles dragging fieldtypes from the source panel onto drop zones.
 * Should be used once at the Builder level.
 */
export function useFieldtypeDraggable({ pages, onDragStart, onDrop }) {
    let draggable = null;
    let lastClientY = 0;

    const dropTarget = createDropTarget();
    const dropIndicator = createDropIndicator();

    const refresh = () => {
        draggable?.destroy();
        init();
    };

    const init = () => {
        draggable?.destroy();

        const fieldtypeSource = document.querySelectorAll('.fieldtype-source-container');
        const dropZones = document.querySelectorAll('.section-drop-zone, .section-gap-drop-zone');
        const containers = [...fieldtypeSource, ...dropZones];

        if (containers.length === 0) return;

        draggable = new Draggable(containers, {
            draggable: '.fieldtype-draggable',
            delay: { mouse: 0, touch: 150 },
            distance: 5,
            mirror: { constrainDimensions: true, appendTo: 'body' },
        });

        draggable.on('drag:start', () => {
            dropTarget.reset();
            onDragStart?.();
        });

        draggable.on('mirror:created', (event) => {
            const width = `${event.source.offsetWidth}px`;
            event.mirror.style.width = width;
            const button = event.mirror.querySelector('button');
            if (button) {
                button.style.width = width;
                button.style.justifyContent = 'flex-start';
            }
        });

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
            event.mirror?.remove();

            if (!target) return;

            handleDrop(target, event.source.dataset.fieldtype);
        });
    };

    const renderIndicator = (event) => {
        const target = dropTarget.effective;
        if (!target) return hideIndicator();

        const isGap = target.classList.contains('section-gap-drop-zone');
        const fieldtype = event.source.dataset.fieldtype;
        const isStructuralDrag = fieldtype === 'section' || fieldtype === 'page_break';
        if (isGap && !isStructuralDrag) return hideIndicator();

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

        const pageEl = target.closest('[data-form-page]');
        if (!pageEl) return;

        const pageId = pageEl.dataset.formPage;
        const page = pages.value.find((p) => p._id === pageId);
        if (!page) return;

        if (target.classList.contains('section-gap-drop-zone')) {
            const sectionIndex = parseInt(target.dataset.sectionGapIndex, 10);
            onDrop({ pageId, fieldtypeHandle, sectionIndex, fieldIndex: null });
            return;
        }

        const sectionId = target.dataset.sectionDropZone;
        if (!sectionId) return;

        const sectionIndex = page.sections.findIndex((s) => s._id === sectionId);
        if (sectionIndex === -1) return;

        const fields = document.querySelectorAll(`[data-section-drop-zone="${sectionId}"] [data-field-item]`);
        const fieldIndex = indexFromClientY(fields, lastClientY);

        onDrop({ pageId, fieldtypeHandle, sectionId, sectionIndex, fieldIndex });
    };

    onMounted(async () => {
        await nextTick();
        await nextTick();
        init();
    });

    onUnmounted(() => {
        draggable?.destroy();
    });

    // Refresh when pages or sections change
    watch(
        () => pages.value.flatMap((p) => p.sections).length,
        () => nextTick(refresh),
    );

    watch(
        () => pages.value.flatMap((p) => p.sections.flatMap((s) => s.fields)).length,
        () => nextTick(refresh),
    );
}

/**
 * Handles sorting/reordering fields within sections.
 * Should be used per-page.
 */
export function useSortable({ container, sections, fieldView, onFieldMoved, onMirrorCreated }) {
    let sortables = [];

    const refresh = () => {
        destroy();
        init();
    };

    const destroy = () => {
        sortables.forEach((sortable) => sortable.destroy());
        sortables = [];
    };

    const init = () => {
        destroy();

        const el = container.value;
        if (!el) return;

        const containers = [...el.querySelectorAll(SORT_CONTAINER_SELECTOR)];
        if (containers.length === 0) return;

        // One Sortable per page so fields can only be dragged within their own page.
        groupContainersByPage(containers).forEach((group) => sortables.push(createSortable(group)));
    };

    const createSortable = (containers) => {
        const sortable = new Sortable(containers, {
            draggable: '[data-field-item]',
            handle: '[data-field-item]',
            distance: 5,
            mirror: { constrainDimensions: true, appendTo: 'body' },
            exclude: { plugins: [Draggable.Plugins.Focusable] },
        });

        sortable.on('drag:start', () => document.documentElement.classList.add('cursor-grabbing'));
        sortable.on('drag:stop', () => document.documentElement.classList.remove('cursor-grabbing'));

        sortable.on('mirror:created', (event) => {
            onMirrorCreated?.(event);

            if (fieldView?.value !== 'collapsed') return;

            event.mirror.querySelectorAll('[data-collapsed-field-icon]').forEach((el) => (el.style.display = 'inline-flex'));

            event.mirror.querySelectorAll('[data-ui-input-group]').forEach((inputGroup) => {
                inputGroup.querySelectorAll('[data-ui-description]').forEach((el) => (el.style.display = 'none'));

                Array.from(inputGroup.children).forEach((child, index) => {
                    if (index === 0) return;
                    if (child.querySelector('[data-logic-attached]')) return;
                    child.style.display = 'none';
                });
            });
        });

        let dragStartState = null;

        sortable.on('sortable:start', (event) => {
            // Capture original DOM state before any manipulation
            const container = event.dragEvent.sourceContainer;
            const children = [...container.querySelectorAll('[data-field-item]')];
            const sourceIndex = children.indexOf(event.dragEvent.source);
            dragStartState = {
                container,
                index: sourceIndex,
                element: event.dragEvent.source,
            };
        });

        sortable.on('sortable:stop', (event) => {
            const { oldIndex, newIndex, oldContainer, newContainer } = event;
            const from = oldContainer.dataset.sortSection;
            const to = newContainer.dataset.sortSection;

            if (!from || !to) {
                dragStartState = null;
                return;
            }
            if (from === to && oldIndex === newIndex) {
                dragStartState = null;
                return;
            }

            // Revert DOM to original state - Vue will handle the update via reactivity
            if (dragStartState) {
                const { container, index, element } = dragStartState;
                element.remove();
                const children = [...container.querySelectorAll('[data-field-item]')];
                if (index < children.length) {
                    container.insertBefore(element, children[index]);
                } else {
                    container.appendChild(element);
                }
            }
            dragStartState = null;

            onFieldMoved(from, to, oldIndex, newIndex);
        });

        return sortable;
    };

    onMounted(async () => {
        await nextTick();
        await nextTick();
        init();
    });

    onUnmounted(destroy);

    watch(
        () => sections.value.map((s) => s.fields.length).join(','),
        () => nextTick(refresh),
    );
}

function groupContainersByPage(containers) {
    const groups = new Map();

    containers.forEach((container) => {
        const page = container.closest('[data-form-page]') ?? 'ungrouped';
        if (!groups.has(page)) groups.set(page, []);
        groups.get(page).push(container);
    });

    return [...groups.values()];
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
