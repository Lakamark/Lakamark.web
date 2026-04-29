import {AppRunner, TurboAppKernel} from "./core";
import {
    DebugModule,
    HeaderModule,
    MenuModule
} from "./modules";

declare global {
    interface Window {
        __lmkKernel?: TurboAppKernel;
    }
}

/**
 * Bootstraps the frontend application.
 *
 * Responsibilities:
 * - Instantiates the application runner with all modules
 * - Creates a Turbo-aware kernel to manage lifecycle
 * - Ensures a single kernel instance across Turbo navigations
 *
 * Behavior:
 * - Prevents multiple initializations using a global guard (`window.__lmkKernel`)
 * - Delegates lifecycle management to {@link TurboAppKernel}
 *
 * Notes:
 * - This function should be called once from the main entry point (`app.ts`)
 * - Modules should be registered here
 *
 * @example
 * ```ts
 * import { bootstrap } from './bootstrap';
 *
 * bootstrap();
 * ```
 */
export function bootstrap(): void {
    // Prevent re-initialization across Turbo navigations
    if (window.__lmkKernel) {
        return;
    }

    // Register application modules here
    const runner = new AppRunner([
        new DebugModule(),
        new HeaderModule(),
        new MenuModule()
    ]);

    // Create and boot the Turbo-aware kernel
    window.__lmkKernel = new TurboAppKernel(runner);
    window.__lmkKernel.boot();
}