export function assertElement<T extends Element>(
    value: Element | null,
    message = "Expected DOM element to exist"
): T {

    if (!value) {
        throw new Error(message);
    }

    return value as T;
}