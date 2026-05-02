/**
 * Safely queries a DOM element.
 *
 * Returns null if not found.
 */
export function queryOptional<T extends Element>(
    root: ParentNode,
    selector: string,
): T | null {
    return root.querySelector(selector) as T | null;
}