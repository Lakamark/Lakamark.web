import {describe, it, expect} from "vitest";
import { AppContext, } from "../../../core";
import {SimpleKernel} from "../../../core/kernel";
import {createFakeContext} from "../../factories";
import {createMockAppRunner} from "../../helpers";

describe('SimpleKernel', (): void => {
    it('mounts the app when booted', (): void => {
        const context: AppContext = createFakeContext();

        const app = createMockAppRunner();

        const kernel = new SimpleKernel(app, context);

        kernel.boot();

        expect(app.mount).toHaveBeenCalledWith(context);
    });

    it('destroys the app when destroyed', (): void => {
        const context: AppContext = createFakeContext();

        const app = createMockAppRunner();

        const kernel = new SimpleKernel(app, context);

        kernel.destroy();

        expect(app.destroy).toHaveBeenCalledTimes(1);
    });
})