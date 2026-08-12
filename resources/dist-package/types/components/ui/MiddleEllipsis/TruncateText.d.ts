/**
 * Observes the parent element for resize events and truncates the text
 * with a middle ellipsis when it overflows.
 *
 * Returns a cleanup function to disconnect the observer.
 */
export default function truncateOnResize(element: any, text: any, { ellipsis }?: {
    ellipsis?: string | undefined;
}): () => void;
