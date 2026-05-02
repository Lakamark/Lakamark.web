import {describe, it, expect} from "vitest";
import {createFakeContext} from "../../factories";
import {AppContext} from "../../../core";
import {TurboKernel} from "../../../core/kernel";
import {createMockAppRunner} from "../../helpers";

describe("TurboKernel", (): void => {
    it('mounts the app immediately when booted', (): void => {
        const context: AppContext = createFakeContext();

        const app = createMockAppRunner();

        const kernel = new TurboKernel(app, context);

        kernel.boot();

        expect(app.mount).toHaveBeenCalledWith(context);
    });

    it('mounts the app on turbo:load', (): void => {
        const context: AppContext = createFakeContext();

        const app = createMockAppRunner();

        const kernel = new TurboKernel(app, context);

        kernel.boot();
        app.mount.mockClear();

        context.document.dispatchEvent(new Event('turbo:load'));

        expect(app.mount).toHaveBeenCalledWith(context);
    });

    it('destroys the app on turbo:before-cache', (): void => {
        const context: AppContext = createFakeContext();

        const app = createMockAppRunner()

        const kernel = new TurboKernel(app, context);

        kernel.boot();

        context.document.dispatchEvent(new Event('turbo:before-cache'));

        expect(app.destroy).toHaveBeenCalledTimes(1);
    });

    it('removes event listeners when destroyed', (): void => {
        const context: AppContext = createFakeContext();

        const app = createMockAppRunner()

        const kernel = new TurboKernel(app, context);

        kernel.boot();
        kernel.destroy();

        app.mount.mockClear();
        app.destroy.mockClear();

        context.document.dispatchEvent(new Event('turbo:load'));
        context.document.dispatchEvent(new Event('turbo:before-cache'));

        expect(app.mount).not.toHaveBeenCalled();
        expect(app.destroy).not.toHaveBeenCalled();
    });
});