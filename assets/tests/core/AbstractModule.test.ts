import { describe, expect, it } from 'vitest';
import {AbstractModule} from "../../core";

class FakeModule extends AbstractModule {
    public mountCount = 0;
    public destroyCount = 0;

    protected onMount(): void {
        this.mountCount++;
    }

    protected onDestroy(): void {
        this.destroyCount++;
    }
}

describe('AbstractModule', () => {
    it('mounts only once', () => {
        const module = new FakeModule();

        module.mount();
        module.mount();

        expect(module.mountCount).toBe(1);
    });

    it('does not destroy before mount', () => {
        const module = new FakeModule();

        module.destroy();

        expect(module.destroyCount).toBe(0);
    });

    it('destroys only once after mount', () => {
        const module = new FakeModule();

        module.mount();
        module.destroy();
        module.destroy();

        expect(module.destroyCount).toBe(1);
    });

    it('can mount again after destroy', () => {
        const module = new FakeModule();

        module.mount();
        module.destroy();
        module.mount();

        expect(module.mountCount).toBe(2);
    });
})