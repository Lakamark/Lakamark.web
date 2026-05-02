import {describe, vi, it, expect} from "vitest";
import {AbstractModule} from "../../core";

class TestModule extends AbstractModule {
    name = 'test-module';

    onMount = vi.fn();
    onDestroy = vi.fn();
}

describe("AbstractModule", (): void => {
    it('calls onMount only once when mounted multiple times', (): void => {
        const module = new TestModule();

        module.mount();
        module.mount();
        module.mount();

        expect(module.onMount).toHaveBeenCalledTimes(1);
    });

    it('does not call onDestroy if not mounted', () => {
        const module = new TestModule();

        module.destroy();

        expect(module.onDestroy).not.toHaveBeenCalled();
    });

    it('calls onDestroy only once after mount', () => {
        const module = new TestModule();

        module.mount();
        module.destroy();
        module.destroy();

        expect(module.onDestroy).toHaveBeenCalledTimes(1);
    });

    it('allows remount after destroy', () => {
        const module = new TestModule();

        module.mount();
        module.destroy();
        module.mount();

        expect(module.onMount).toHaveBeenCalledTimes(2);
    });

    it('keeps lifecycle consistent (mount → destroy → mount → destroy)', () => {
        const module = new TestModule();

        module.mount();
        module.destroy();
        module.mount();
        module.destroy();

        expect(module.onMount).toHaveBeenCalledTimes(2);
        expect(module.onDestroy).toHaveBeenCalledTimes(2);
    });
})