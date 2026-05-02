import {AppModule} from "./contracts";

/**
 * Central orchestrator of the frontend application.
 *
 * The AppRunner is responsible for managing the lifecycle of all modules.
 * It does not contain any business logic or DOM interaction itself.
 *
 * Responsibilities:
 * - Register modules (features)
 * - Mount all modules when the application starts
 * - Destroy all modules when the application is torn down
 *
 * Execution order:
 * - Modules are mounted in the order they are registered
 * - Modules are destroyed in reverse order (LIFO)
 *
 * Why reverse destroy?
 * → Prevent dependency issues between modules
 *   (e.g. a MenuModule depending on HeaderModule)
 *
 * Design constraints:
 * - No DOM access (delegated to modules)
 * - No framework coupling (Turbo handled elsewhere)
 * - Pure orchestration layer
 *
 * Example:
 * ```ts
 * const app = new AppRunner()
 *   .register(new ThemeModule())
 *   .register(new HeaderModule());
 *
 * app.mount();   // mount all modules
 * app.destroy(); // cleanup all modules
 * ```
 */
export class AppRunner {
    private readonly modules: AppModule[] = [];

    /**
     * Registers a module into the application.
     *
     * Modules are executed in the order they are registered.
     *
     * @param module - Module instance to register
     * @returns The current AppRunner instance (fluent API)
     */
    register(module: AppModule): this {
        this.modules.push(module);

        return this;
    }

    /**
     * Mounts all registered modules.
     *
     * Called when the application starts or when a page is loaded.
     */
    mount(): void {
        for (const module of this.modules) {
            module.mount();
        }
    }

    /**
     * Destroys all registered modules in reverse order.
     *
     * Called before navigation or when the application is torn down.
     */
    destroy(): void {
        for (const module of [...this.modules].reverse()) {
            module.destroy();
        }
    }
}