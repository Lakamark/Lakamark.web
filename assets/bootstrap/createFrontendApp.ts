import {FrontendKernel} from "../app/core/kernel/FrontendKernel";

export function createFrontendApp(): FrontendKernel {
    return new FrontendKernel();
}