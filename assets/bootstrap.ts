import { AppRunner, TurboAppKernel } from './core';
import {
    DebugModule,
    HeaderModule,
    MenuModule,
    ThemeModule,
} from './modules';

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
 */
export function bootstrap(): void {
    if (window.__lmkKernel) {
        window.__lmkKernel.destroy();
    }

    const runner = new AppRunner([
        new DebugModule(),
        new HeaderModule(),
        new MenuModule(),
        new ThemeModule(),
    ]);

    window.__lmkKernel = new TurboAppKernel(runner);
    window.__lmkKernel.boot();
}

export {};