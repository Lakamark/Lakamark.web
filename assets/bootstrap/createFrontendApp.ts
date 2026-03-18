import {FrontendKernel} from "../app/core/kernel/FrontendKernel";

/**
 * Creates a new frontend application instance.
 *
 * This is the main entry point used to initialize the frontend runtime.
 * It returns a fresh FrontendKernel instance that can be configured
 * by registering modules before calling `boot()`.
 *
 * @returns A new FrontendKernel instance
 *
 * @example
 * const app = createFrontendApp();
 *
 * app
 *   .registerModule(new CarouselModule())
 *   .boot();
 */
export function createFrontendApp(): FrontendKernel {
    return new FrontendKernel();
}