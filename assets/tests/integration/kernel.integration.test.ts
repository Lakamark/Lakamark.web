import { describe, expect, it, vi } from 'vitest';
import { AppRunner, AbstractModule } from '../../core';
import { TurboKernel } from '../../core/kernel';
import { createFakeContext } from '../factories';
import type { AppContext } from '../../core';

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

describe('Integration: Kernel → AppRunner → Modules', (): void => {
    it('mounts and destroys modules through Turbo lifecycle', (): void => {
        const context: AppContext = createFakeContext();

        const module = new TestModule();

        const app: AppRunner = new AppRunner()
            .register(module);

        const kernel = new TurboKernel(app, context);

        // initial boot
        kernel.boot();

        expect(module.onMountSpy).toHaveBeenCalledTimes(1);

        // simulate navigation
        context.document.dispatchEvent(new Event('turbo:before-cache'));

        expect(module.onDestroySpy).toHaveBeenCalledTimes(1);

        // simulate next page load
        context.document.dispatchEvent(new Event('turbo:load'));

        expect(module.onMountSpy).toHaveBeenCalledTimes(2);
    });
})