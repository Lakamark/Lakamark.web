/**
 * Safely queries a required DOM element.
 * Throws an error if the element is not found.
 *
 * @template T
 * @param root - Root node to search within
 * @param selector - CSS selector
 * @returns The found element
 * @throws {Error} If no element matches the selector
 */
export function queryRequired<T extends Element = Element>(
    root: ParentNode,
    selector: string,
): T {
    const element = root.querySelector<T>(selector);

    if (!element) {
        throw new Error(`Required DOM element not found: ${selector}`);
    }

    return element;
}

/**
 * Safely queries an optional DOM element.
 *
 * @template T
 * @param root - Root node to search within
 * @param selector - CSS selector
 * @returns The found element or null
 */
export function queryOptional<T extends Element = Element>(
    root: ParentNode,
    selector: string,
): T | null {
    return root.querySelector<T>(selector);
}