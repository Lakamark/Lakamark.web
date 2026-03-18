import {AppRunner} from "../runner/AppRunner";
import {AppModuleInterface} from "../module/AppModuleInterface";

/**
 * Central entry point responsible for managing the frontend module lifecycle.
 *
 * The FrontendKernel coordinates module registration and delegates runtime
 * execution to the AppRunner.
 *
 * Typical lifecycle:
 * - create the kernel
 * - register one or many modules
 * - boot the kernel
 * - destroy the kernel when cleanup is needed
 *
 * The kernel should be initialized only after the application configuration
 * has been parsed and validated.
 *
 * @example
 * const kernel = new FrontendKernel();
 *
 * kernel
 *   .registerModule(new CarouselModule())
 *   .registerModule(new ModalModule());
 *
 * kernel.boot();
 */
export class FrontendKernel {
    private readonly runner: AppRunner;

    public constructor(runner: AppRunner = new AppRunner()) {
        this.runner = runner;
    }

    /**
     * Registers a single module in the kernel.
     *
     * Registered modules are stored by the underlying AppRunner and will be
     * mounted when the kernel boots.
     *
     * @param module - Frontend module to register
     *
     * @returns The current kernel instance for chaining
     */
    public registerModule(module: AppModuleInterface): this {
        this.runner.register(module);

        return this;
    }

    /**
     * Registers multiple modules in the kernel.
     *
     * All modules must implement AppModuleInterface.
     *
     * @param modules - List of frontend modules to register
     *
     * @returns The current kernel instance for chaining
     */
    public registerModules(modules: AppModuleInterface[]): this {
        this.runner.registerMany(modules);

        return this;
    }

    /**
     * Boots the kernel and mounts all registered modules.
     *
     * This starts the frontend runtime lifecycle.
     */
    public boot(): void {
        this.runner.mount();
    }

    /**
     * Destroys the kernel and delegates cleanup to the runner.
     *
     * This should be used when registered modules need to release resources
     * such as event listeners, observers or other side effects.
     */
    public destroy(): void {
        this.runner.destroy();
    }

    /**
     * Indicates whether the kernel has already been booted.
     *
     * @returns `true` if the kernel is mounted, otherwise `false`
     */
    public isBooted(): boolean {
        return this.runner.isMounted();
    }

    /**
     * Returns the list of currently registered modules.
     *
     * @returns A readonly collection of registered modules
     */
    public getModules(): readonly AppModuleInterface[] {
        return this.runner.getModules();
    }
}