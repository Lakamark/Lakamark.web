/**
 * Contract for objects that expose a destruction lifecycle.
 *
 * Typically used for instances that allocate resources such as:
 * - event listeners
 * - observers
 * - DOM bindings
 *
 * Implementations must ensure proper cleanup when `destroy()` is called.
 */
export interface DestroyableInterface {
    /**
     * Releases all resources held by the instance.
     */
    destroy(): void;
}