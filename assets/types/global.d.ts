import {AppKernel} from "../core";

declare global {
    interface Window {
        __lmkKernel?: AppKernel
    }
}

export {}