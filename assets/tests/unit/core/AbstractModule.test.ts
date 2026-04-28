import { describe, expect, it } from 'vitest';
import {AppConfig} from "../../../dom";
import {createFakeConfig, FakeModule} from "../../Fake";

describe('AbstractModule', () => {
    it('mounts only once', () => {
        const module = new FakeModule();
        const config: AppConfig = createFakeConfig();

        module.mount(config);
        module.mount(config);

        expect(module.mountCount).toBe(1);
    });

    it('does not destroy before mount', () => {
        const module = new FakeModule();

        module.destroy();

        expect(module.destroyCount).toBe(0);
    });

    it('destroys only once after mount', () => {
        const module = new FakeModule();
        const config: AppConfig = createFakeConfig();

        module.mount(config);
        module.destroy();
        module.destroy();

        expect(module.destroyCount).toBe(1);
    });

    it('can mount again after destroy', () => {
        const module = new FakeModule();
        const config: AppConfig = createFakeConfig();

        module.mount(config);
        module.destroy();
        module.mount(config);

        expect(module.mountCount).toBe(2);
    });
})