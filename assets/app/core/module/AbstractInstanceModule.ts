import {DestroyableInterface} from "./DestroyableInterface";
import {AppRunner} from "../runner/AppRunner";
import {AbstractModule} from "./AbstractModule";

/**
 * Base class for modules that manage a single instance lifecycle.
 *
 * This abstract module provides a standard lifecycle for:
 * - creating an instance when the module is mounted
 * - optionally executing logic after the instance is created
 * - cleaning up the instance when the module is destroyed
 *
 * It follows a template method pattern where subclasses must implement
 * instance creation and can hook into lifecycle steps.
 *
 * @template T - Instance type that must implement DestroyableInterface
 */
export abstract class AbstractInstanceModule<T extends DestroyableInterface> extends AbstractModule {
    protected instance: T | null = null;

    /**
     * Called internally when the module is mounted.
     *
     * This method:
     * - creates the instance
     * - stores it
     * - calls the post-mount hook
     */
    protected onMount(runner: AppRunner) {
        this.instance = this.createInstance(runner);
        this.afterInstanceMount(this.instance, runner);
    }

    /**
     * Called internally when the module is destroyed.
     *
     * This method:
     * - calls the pre-destroy hook
     * - destroys the instance
     * - clears the reference
     */
    protected onDestroy(): void {
        this.beforeInstanceDestroy(this.instance);
        this.instance?.destroy();
        this.instance = null;
    }

    /**
     * Creates the instance associated with this module.
     *
     * This method must be implemented by subclasses.
     *
     * @param runner - The application runner
     *
     * @returns A destroyable instance
     */
    protected abstract createInstance(runner: AppRunner): T;

    /**
     * Hook executed after the instance has been created and mounted.
     *
     * Can be overridden to add custom logic.
     *
     * @param _instance - The created instance
     * @param _runner - The application runner
     */
    protected afterInstanceMount(_instance: T, _runner: AppRunner): void {
    }

    /**
     * Hook executed before the instance is destroyed.
     *
     * Can be overridden to perform cleanup or side effects.
     *
     * @param _instance - The instance about to be destroyed (may be null)
     */
    protected beforeInstanceDestroy(_instance: T | null): void {
    }
}