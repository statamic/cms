/**
 * Show a count badge next to the cursor while several assets are being dragged.
 *
 * Browsers render drag images semi-transparent, so the native drag image is swapped for
 * an empty one and the badge is a regular element that follows the cursor instead.
 */
export function showMultipleAssetDragBadge(event, count) {
    hideNativeDragImage(event);

    const badge = document.createElement('div');
    badge.className =
        'pointer-events-none fixed top-0 left-0 z-[9999] flex items-center justify-center h-7 min-w-7 rounded-full bg-blue-500 px-2 text-xs font-semibold text-white';
    badge.textContent = count;
    document.body.appendChild(badge);

    // Tuck in at the bottom right of the cursor, like Finder, so it doesn't cover what you're dropping onto.
    const follow = ({ clientX, clientY }) => {
        badge.style.transform = `translate(${clientX + 18}px, ${clientY + 20}px)`;
    };

    const cleanUp = () => {
        badge.remove();
        document.removeEventListener('dragover', follow, true);
        document.removeEventListener('dragend', cleanUp, true);
        document.removeEventListener('drop', cleanUp, true);
    };

    follow(event);
    // Capture, so elements that stop drag events from bubbling (like the uploader) can't hide them.
    document.addEventListener('dragover', follow, true);
    document.addEventListener('dragend', cleanUp, true);
    document.addEventListener('drop', cleanUp, true);
}

function hideNativeDragImage(event) {
    // The element has to be in the DOM when it's handed to setDragImage(),
    // and can be removed once the browser has taken its snapshot.
    const empty = document.createElement('div');
    empty.className = 'fixed -top-[1000px] size-px';
    document.body.appendChild(empty);

    event.dataTransfer.setDragImage(empty, 0, 0);
    setTimeout(() => empty.remove());
}
