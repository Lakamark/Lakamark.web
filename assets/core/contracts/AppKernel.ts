/**
 * Controls the application lifecycle.
 *
 * A kernel decides when the application should mount or destroy itself.
 * It does not contain feature logic; feature logic belongs to modules.
 */
export interface AppKernel {
    /**
     * Starts the application lifecycle.
     */
    boot(): void;

    /**
     * Stops the application lifecycle and cleans up listeners/state.
     */
    destroy(): void;
}