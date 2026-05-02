import {Module} from "node:vm";
import {AppModule} from "./contracts";

/**
 * Base class for frontend modules that need a safe lifecycle.
 *
 * This class protects modules from being mounted or destroyed multiple times.
 * Feature modules should extend this class instead of implementing
 * AppModule directly when they need standard lifecycle behavior.
 *
 * Lifecycle rules:
 * - mount() calls onMount() only once
 * - destroy() calls onDestroy() only if the module was mounted
 * - after destroy(), the module can be mounted again
 *
 * Subclasses should put their real setup/cleanup logic inside:
 * - onMount(): add listeners, initialize DOM behavior, create instances
 * - onDestroy(): remove listeners, destroy instances, reset references
 *
 * Example:
 * ```ts
 * class HeaderModule extends AbstractModule {
 *   readonly name = 'header';
 *
 *   protected onMount(): void {
 *     // initialize header behavior
 *   }
 *
 *   protected onDestroy(): void {
 *     // remove listeners / cleanup
 *   }
 * }
 * ```
 */
export abstract class AbstractModule implements AppModule {
    private mounted: boolean = false;

    abstract readonly name: string;

    /**
     * Mounts the module once.
     *
     * If the module is already mounted, this method does nothing.
     */
    mount(): void {
        if (this.mounted) {
            return;
        }

        this.onMount();
        this.mounted = true;
    }

    /**
     * Destroys the module once.
     *
     * If the module is not mounted, this method does nothing.
     */
    destroy(): void {
        if (!this.mounted) {
            return;
        }

        this.onDestroy();
        this.mounted = false;
    }

    /**
     * Setup hook implemented by the concrete module.
     */
    protected abstract onMount(): void;

    /**
     * Optional cleanup hook implemented by the concrete module.
     */
    protected onDestroy(): void {
        // Optional cleanup.
    }
}