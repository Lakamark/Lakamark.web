import {AppContext} from "./AppContext";

/**
 * Represents a pluggable unit of functionality within the application.
 *
 * A module encapsulates a single feature (e.g. theme, header, menu)
 * and is managed by the {@link AppRunner} lifecycle.
 *
 * Responsibilities:
 * - Initialize feature-specific logic when mounted
 * - Interact only through the provided {@link AppContext}
 * - Clean up all side effects when destroyed
 *
 * Lifecycle:
 * - {@link mount} is called once per application boot or page load
 * - {@link destroy} is called before teardown or page navigation (e.g. Turbo)
 *
 * Constraints:
 * - Must be self-contained (no implicit global access)
 * - Must not rely on shared mutable state outside of {@link AppContext}
 * - Must fully clean up (event listeners, timers, DOM mutations, instances)
 *
 * Design goals:
 * - Promote modular architecture and separation of concerns
 * - Enable dependency injection via {@link AppContext}
 * - Improve testability by avoiding direct global usage (window, document)
 * - Support re-mounting scenarios (e.g. Turbo navigation)
 *
 * Example:
 * ```ts
 * class ThemeModule implements AppModule {
 *   readonly name = 'theme';
 *
 *   mount(context: AppContext): void {
 *     const { document, config } = context;
 *     // initialize theme based on config
 *   }
 *
 *   destroy(): void {
 *     // cleanup listeners / instances
 *   }
 * }
 * ```
 */
export interface AppModule {
    /**
     * Unique identifier of the module.
     * Used for debugging, logging, and module management.
     */
    readonly name: string;

    /**
     * Mounts the module and initializes its behavior.
     *
     * @param context - The application context providing access
     * to configuration, DOM, and environment.
     */
    mount(context: AppContext): void;

    /**
     * Cleans up all side effects created during {@link mount}.
     *
     * This includes:
     * - Event listeners
     * - Timers / intervals
     * - DOM mutations
     * - External instances
     */
    destroy(): void;
}