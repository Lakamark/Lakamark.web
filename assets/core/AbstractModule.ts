import type { AppConfig } from '../dom';

/**
 * Base class for all frontend modules.
 *
 * Handles lifecycle:
 * - mount (once)
 * - destroy (once after mount)
 *
 * Ensures idempotent behavior:
 * - mount() cannot run twice without destroy()
 * - destroy() does nothing if not mounted
 *
 * Modules should extend this class and implement
 * `onMount` / `onDestroy` hooks.
 */
export abstract class AbstractModule {
    /**
     * Internal mounted state flag.
     *
     * @internal
     */
    private mounted: boolean = false;

    /**
     * Mounts the module.
     *
     * This method is idempotent:
     * calling it multiple times will only trigger `onMount` once,
     * unless `destroy()` has been called.
     *
     * @param config - Global application configuration
     */
    mount(config: AppConfig): void {
        if (this.mounted) {
            return;
        }

        this.mounted = true;
        this.onMount(config);
    }

    /**
     * Destroys the module.
     *
     * This method is safe to call even if the module is not mounted.
     */
    destroy(): void {
        if (!this.mounted) return;

        this.onDestroy();
        this.mounted = false;
    }

    /**
     * Lifecycle hook called once when the module is mounted.
     *
     * Override this method in child classes to implement
     * initialization logic.
     *
     * @param _config - Global application configuration
     */
    protected onMount(_config: AppConfig): void {}

    /**
     * Lifecycle hook called once when the module is destroyed.
     *
     * Override this method in child classes to implement
     * cleanup logic.
     */
    protected onDestroy(): void {}
}