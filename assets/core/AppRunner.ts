import { AbstractModule } from './AbstractModule';
import type { AppConfig } from '../dom';

/**
 * Orchestrates the lifecycle of application modules.
 *
 * Responsibilities:
 * - Mount all modules in the provided order
 * - Destroy all modules in reverse order (LIFO)
 *
 * Lifecycle integration:
 * Designed to work with navigation systems like Turbo:
 * - `mount()` should be called on page load
 * - `destroy()` should be called before cache / navigation
 *
 * Notes:
 * - Modules are expected to extend {@link AbstractModule}
 * - Each module handles its own idempotency (mount/destroy safety)
 *
 * @example
 * ```ts
 * const app = new AppRunner([
 *   new ThemeModule(),
 *   new HeaderModule(),
 * ]);
 *
 * document.addEventListener('turbo:load', () => {
 *   app.mount(config);
 * });
 *
 * document.addEventListener('turbo:before-cache', () => {
 *   app.destroy();
 * });
 * ```
 */
export class AppRunner {
    /**
     * @param modules - List of modules to orchestrate
     */
    constructor(
        private modules: AbstractModule[]
    ) {}

    /**
     * Mounts all registered modules.
     *
     * Modules are mounted in the order they are provided.
     *
     * @param config - Global application configuration
     */
    mount(config: AppConfig): void {
        this.modules.forEach((module: AbstractModule): void => module.mount(config));
    }

    /**
     * Destroys all registered modules.
     *
     * Modules are destroyed in reverse order to ensure
     * proper teardown of dependencies.
     */
    destroy(): void {
        [...this.modules]
            .reverse()
            .forEach((module: AbstractModule): void => module.destroy());
    }
}