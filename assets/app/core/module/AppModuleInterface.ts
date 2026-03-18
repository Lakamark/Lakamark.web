import {AppRunner} from "../runner/AppRunner";

/**
 * Contract for all frontend modules managed by the AppRunner.
 *
 * A module must implement a lifecycle with:
 * - mount: initialization logic
 * - destroy: cleanup logic
 * - isMounted: runtime state check
 *
 * Modules are registered in the AppRunner and executed
 * during the FrontendKernel lifecycle.
 */
export interface AppModuleInterface {
    /**
     * Mounts the module and initializes its behavior.
     *
     * @param runner - The application runner orchestrating modules
     */
    mount(runner: AppRunner): void;

    /**
     * Cleans up the module and releases any allocated resources.
     */
    destroy(): void;

    /**
     * Indicates whether the module is currently mounted.
     *
     * @returns `true` if mounted, otherwise `false`
     */
    isMounted(): boolean
}