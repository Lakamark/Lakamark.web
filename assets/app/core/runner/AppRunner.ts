import {AppModuleInterface} from "../module/AppModuleInterface";

/**
 * Internal runtime orchestrator responsible for managing module execution.
 *
 * The AppRunner handles:
 * - module registration
 * - mounting all modules
 * - destroying modules in reverse order
 * - tracking runtime state
 *
 * It is used internally by the FrontendKernel and should not be directly
 * coupled to application business logic.
 *
 * Lifecycle:
 *
 * register() / registerMany()
 *   → store modules
 *
 * mount()
 *   → call module.mount() for each module
 *   → set mounted = true
 *
 * destroy()
 *   → call module.destroy() in reverse order
 *   → set mounted = false
 */
export class AppRunner {
    private readonly modules: AppModuleInterface[] = [];
    private mounted = false;

    /**
     * Registers a module if it has not already been registered.
     *
     * Duplicate module instances are ignored.
     *
     * @param module - Module instance to register
     *
     * @returns The current runner instance for chaining
     */
    public register(module: AppModuleInterface): this {
        if (this.modules.includes(module)) {
            return this;
        }

        this.modules.push(module);

        return this;
    }

    /**
     * Registers multiple modules.
     *
     * Internally delegates to `register()` to ensure deduplication.
     *
     * @param modules - List of modules to register
     *
     * @returns The current runner instance for chaining
     */
    public registerMany(modules: AppModuleInterface[]): this {
        for (const module of modules) {
            this.register(module);
        }

        return this;
    }

    /**
     * Mounts all registered modules.
     *
     * Each module's `mount()` method is called in registration order.
     * This method is idempotent and will not re-run if already mounted.
     */
    public mount(): void {
        if (this.mounted) {
            return;
        }

        for (const module of this.modules) {
            module.mount(this);
        }

        this.mounted = true;
    }

    /**
     * Destroys all registered modules.
     *
     * Modules are destroyed in reverse order of registration to ensure
     * proper dependency cleanup (last mounted → first destroyed).
     */
    public destroy(): void {
        if (!this.mounted) {
            return;
        }

        for (const module of [...this.modules].reverse()) {
            module.destroy();
        }

        this.mounted = false;
    }


    /**
     * Indicates whether the runner is currently mounted.
     *
     * @returns `true` if mounted, otherwise `false`
     */
    public isMounted(): boolean {
        return this.mounted;
    }

    /**
     * Returns all registered modules.
     *
     * @returns A readonly array of modules
     */
    public getModules(): readonly AppModuleInterface[] {
        return this.modules;
    }

    /**
     * Returns the number of registered modules.
     *
     * @returns Module count
     */
    public getModuleCount(): number {
        return this.modules.length;
    }
}