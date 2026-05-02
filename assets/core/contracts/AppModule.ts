/**
 * Represents a pluggable unit of functionality in the frontend application.
 *
 * A module is responsible for a specific feature (e.g. theme, header, menu)
 * and is managed by the AppRunner lifecycle.
 *
 * Lifecycle:
 * - mount(): called when the application starts or when the page is loaded
 * - destroy(): called before navigation or when the application is torn down
 *
 * Constraints:
 * - A module should be self-contained (no hidden global side effects)
 * - A module must clean up everything it creates (events, DOM, timers)
 *
 * Design goals:
 * - Decouple features from the core application
 * - Enable easy testing and replacement
 * - Support Turbo-driven page lifecycle (mount / destroy)
 *
 * Example:
 * ```ts
 * class ThemeModule implements AppModule {
 *   name = 'theme';
 *
 *   mount() {
 *     // attach listeners, init UI
 *   }
 *
 *   destroy() {
 *     // remove listeners, cleanup DOM
 *   }
 * }
 * ```
 */
export interface AppModule {
    readonly name: string;

    mount(): void;

    destroy(): void;
}