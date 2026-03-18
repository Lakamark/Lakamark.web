import {AppModuleInterface} from "./AppModuleInterface";
import {AppRunner} from "../runner/AppRunner";

/**
 * Base abstract implementation for all frontend modules.
 *
 * This class provides a standardized lifecycle for modules:
 * - mounting (initialization)
 * - destruction (cleanup)
 *
 * It ensures that:
 * - a module is mounted only once
 * - lifecycle errors are safely handled
 * - internal state remains consistent
 *
 * Subclasses must implement the `onMount` method and may optionally
 * override `onDestroy` to handle cleanup logic.
 *
 * Lifecycle:
 *
 * mount():
 *   → onMount()
 *   → (set mounted = true)
 *
 * destroy():
 *   → onDestroy()
 *   → (set mounted = false)
 */
export abstract class AbstractModule implements AppModuleInterface {
    private mounted = false;


    /**
     * Mounts the module if it is not already mounted.
     *
     * This method is called by the AppRunner during the kernel boot phase.
     * It wraps the internal `onMount` hook with safety checks and error handling.
     *
     * @param runner - The application runner coordinating module execution
     */
    public mount(runner: AppRunner): void {
        if (this.mounted) {
            return;
        }

        try {
            this.onMount(runner);
            this.mounted = true;
        } catch (error) {
            console.log(
                `[Module Error] ${this.getName()} failed during mount`,
                error
            )
        }

    }


    /**
     * Destroys the module if it is currently mounted.
     *
     * This method calls the optional `onDestroy` hook and resets
     * the internal mounted state.
     */
    public destroy(): void {
        if (!this.mounted) {
            return;
        }

        this.onDestroy();
        this.mounted = false;
    }

    /**
     * Indicates whether the module is currently mounted.
     *
     * @returns `true` if the module is mounted, otherwise `false`
     */
    public isMounted(): boolean {
        return this.mounted;
    }

    /**
     * Returns the module name.
     *
     * By default, this is the class name and is mainly used for debugging
     * and logging purposes.
     *
     * @returns The module name
     */
    public getName(): string {
        return this.constructor.name;
    }

    /**
     * Lifecycle hook called when the module is mounted.
     *
     * This method must be implemented by subclasses and should contain
     * initialization logic (e.g. DOM bindings, instance creation, etc.).
     *
     * @param runner - The application runner
     */
    protected abstract onMount(runner: AppRunner): void;

    /**
     * Lifecycle hook called when the module is destroyed.
     *
     * Can be overridden to perform cleanup such as removing event listeners,
     * disconnecting observers, or releasing resources.
     */
    protected onDestroy(): void {
    }
}