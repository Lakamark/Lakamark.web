import {FrontendKernel} from "@core/kernel/FrontendKernel";

export function createFrontendApp(): FrontendKernel {
    return new FrontendKernel();
}