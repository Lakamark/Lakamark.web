import {describe, vi, it, expect, Mock} from "vitest";
import {AppContext, AppModule, AppRunner} from "../../core";
import {createFakeContext} from "../factories";

type MockModule = AppModule & {
    mount: Mock<(context: AppContext) => void>;
    destroy: Mock<() => void>;
};
function createMockModule(name: string): MockModule {
    return {
        name,
        mount: vi.fn(),
        destroy: vi.fn(),
    };
}

describe('AppModule', (): void => {
    it('registers modules using a fluent API', (): void => {
        const runner = new AppRunner();
        const module = createMockModule('theme');

        const result = runner.register(module);

        expect(result).toBe(runner);
    });

    it('mounts registered modules in registration order', (): void => {
        const context = createFakeContext();
        const theme = createMockModule('theme');
        const header = createMockModule('header');

        const runner = new AppRunner()
            .register(theme)
            .register(header);

        runner.mount(context);

        expect(theme.mount).toHaveBeenCalledTimes(1);
        expect(header.mount).toHaveBeenCalledTimes(1);

        expect(theme.mount.mock.invocationCallOrder[0])
            .toBeLessThan(header.mount.mock.invocationCallOrder[0]);
    });

    it('destroys registered modules in reverse registration order', (): void => {
        const theme = createMockModule('theme');
        const header = createMockModule('header');

        const runner = new AppRunner()
            .register(theme)
            .register(header);

        runner.destroy();

        expect(header.destroy).toHaveBeenCalledTimes(1);
        expect(theme.destroy).toHaveBeenCalledTimes(1);

        expect(header.destroy.mock.invocationCallOrder[0])
            .toBeLessThan(theme.destroy.mock.invocationCallOrder[0]);
    });

    it('does not throw when no modules are registered', (): void => {
        const context = createFakeContext();
        const runner = new AppRunner();

        expect(() => runner.mount(context)).not.toThrow();
        expect(() => runner.destroy()).not.toThrow();
    });
})
