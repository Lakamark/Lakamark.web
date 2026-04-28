import {AppRunner, TurboAppKernel} from "./core";
import {DebugModule} from "./modules";

declare global {
    interface Window {
        __lmkKernel?: TurboAppKernel;
    }
}

export function bootstrap(): void {
    if (window.__lmkKernel) {
        return;
    }

    const runner = new AppRunner([
        new DebugModule(),
    ]);

    window.__lmkKernel = new TurboAppKernel(runner);
    window.__lmkKernel.boot();
}