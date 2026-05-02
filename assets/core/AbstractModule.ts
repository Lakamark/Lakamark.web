import {
    AppContext,
    AppModule
} from "./contracts";

/**
 * Base class for frontend modules that need a safe lifecycle.
 *
 * This class prevents modules from being mounted or destroyed multiple times.
 * Feature modules should extend it when they need standard lifecycle behavior.
 *
 * Lifecycle rules:
 * - mount() calls onMount(context) only once
 * - destroy() calls onDestroy() only if the module was mounted
 * - after destroy(), the module can be mounted again
 *
 * Subclasses should put their real setup/cleanup logic inside:
 * - onMount(context): add listeners, initialize DOM behavior, create instances
 * - onDestroy(): remove listeners, destroy instances, reset references
 */
export abstract class AbstractModule implements AppModule {
    private mounted: boolean = false;

    abstract readonly name: string;

    mount(context: AppContext): void {
        if (this.mounted) {
            return;
        }

        this.onMount(context);
        this.mounted = true;
    }

    destroy(): void {
        if (!this.mounted) {
            return;
        }

        this.onDestroy();
        this.mounted = false;
    }

    protected abstract onMount(context: AppContext): void;

    protected onDestroy(): void {
        // Optional cleanup.
    }
}