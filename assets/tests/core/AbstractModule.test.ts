import { describe, expect, it, vi } from 'vitest';
import {AbstractModule, AppContext} from '../../core';
import { createFakeContext } from '../factories';

class TestModule extends AbstractModule {
    readonly name = 'test-module';

    public onMountSpy = vi.fn();
    public onDestroySpy = vi.fn();

    protected onMount(context: AppContext): void {
        this.onMountSpy(context);
    }

    protected onDestroy(): void {
        this.onDestroySpy();
    }
}

class BrokenModule extends AbstractModule {
    readonly name = 'broken-module';

    public readonly onMountSpy = vi.fn();
    public readonly onDestroySpy = vi.fn();

    protected onMount(context: AppContext): void {
        this.onMountSpy(context);
        throw new Error('boot failed');
    }

    protected onDestroy(): void {
        this.onDestroySpy();
    }
}

describe('AbstractModule', (): void => {
    it('passes context to onMount', (): void => {
        const module = new TestModule();
        const context = createFakeContext();

        module.mount(context);

        expect(module.onMountSpy).toHaveBeenCalledWith(context);
    });

    it('mounts only once', (): void => {
        const module = new TestModule();
        const context = createFakeContext();

        module.mount(context);
        module.mount(context);
        module.mount(context);

        expect(module.onMountSpy).toHaveBeenCalledTimes(1);
    });

    it('does not destroy when never mounted', (): void => {
        const module = new TestModule();

        module.destroy();

        expect(module.onDestroySpy).not.toHaveBeenCalled();
    });

    it('destroys only once', (): void => {
        const module = new TestModule();
        const context = createFakeContext();

        module.mount(context);
        module.destroy();
        module.destroy();

        expect(module.onDestroySpy).toHaveBeenCalledTimes(1);
    });

    it('allows remount after destroy', (): void => {
        const module = new TestModule();
        const context = createFakeContext();

        module.mount(context);
        module.destroy();
        module.mount(context);

        expect(module.onMountSpy).toHaveBeenCalledTimes(2);
    });

    it('does not mark as mounted when onMount throws', (): void => {

        const module = new BrokenModule();
        const context = createFakeContext();

        expect(() => module.mount(context)).toThrow('boot failed');

        module.destroy();

        expect(module.onDestroySpy).not.toHaveBeenCalled();
    });
});