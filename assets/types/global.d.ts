// assets/types/global.d.ts

import type { TurboAppKernel } from '../core';

declare global {
    interface Window {
        __lmkKernel?: TurboAppKernel;
    }
}

export {};