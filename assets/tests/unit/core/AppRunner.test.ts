import { describe, expect, it } from 'vitest';
import {AbstractModule, AppRunner} from "../../../core";
import {createFakeConfig} from "../../Fake";
import {AppConfig} from "../../../dom";

class FakeModule extends AbstractModule {
    constructor(
        private readonly name: string,
        private readonly calls: string[],
    ) {
        super();
    }

    protected onMount(): void {
        this.calls.push(`mount:${this.name}`);
    }

    protected onDestroy(): void {
        this.calls.push(`destroy:${this.name}`);
    }
}

describe('AppRunner', () => {
    it('mounts all modules in order', () => {
        const calls: string[] = [];
        const config: AppConfig = createFakeConfig();

        const app = new AppRunner([
            new FakeModule('theme', calls),
            new FakeModule('header', calls),
        ]);

        app.mount(config);

        expect(calls).toEqual([
            'mount:theme',
            'mount:header',
        ]);
    });

    it('destroys all modules in reverse order', () => {
        const calls: string[] = [];
        const config: AppConfig = createFakeConfig();

        const app = new AppRunner([
            new FakeModule('theme', calls),
            new FakeModule('header', calls),
        ]);

        app.mount(config);
        app.destroy();

        expect(calls).toEqual([
            'mount:theme',
            'mount:header',
            'destroy:header',
            'destroy:theme',
        ]);
    });
});