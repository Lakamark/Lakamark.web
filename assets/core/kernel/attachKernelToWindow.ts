import {AppKernel} from "../contracts";

/**
 * Attaches the kernel instance to the global window.
 *
 * Ensures:
 * - only one kernel instance is active
 * - previous instance is properly destroyed
 */
export function attachKernelToWindow(kernel: AppKernel): void {
    window.__lmkKernel?.destroy();
    window.__lmkKernel = kernel;
}