import { beforeEach, describe, expect, it, vi } from "vitest";
import {AppRunner} from "../../../app/core/runner/AppRunner";
import {AppModuleInterface} from "../../../app/core/module/AppModuleInterface";

describe("AppRunner", () => {
    let runner: AppRunner;

    const createModuleMock = (
        label: string,
        calls: string[] = []
    ): {
        module: AppModuleInterface;
        mount: ReturnType<typeof vi.fn>;
        destroy: ReturnType<typeof vi.fn>;
        isMounted: ReturnType<typeof vi.fn>;
    } => {
        const mount = vi.fn((_: AppRunner) => {
            calls.push(`mount:${label}`);
        });

        const destroy = vi.fn(() => {
            calls.push(`destroy:${label}`);
        });

        const isMounted = vi.fn(() => false);

        const module: AppModuleInterface = {
            mount,
            destroy,
            isMounted
        };

        return {
            module,
            mount,
            destroy,
            isMounted
        };
    };

    beforeEach(() => {
        runner = new AppRunner();
    });

    describe("register", () => {
        it("should register a module", () => {
            const moduleA = createModuleMock("A");

            const result = runner.register(moduleA.module);

            expect(result).toBe(runner);
            expect(runner.getModules()).toEqual([moduleA.module]);
            expect(runner.getModuleCount()).toBe(1);
        });

        it("should ignore duplicate module instances", () => {
            const moduleA = createModuleMock("A");

            runner.register(moduleA.module);
            runner.register(moduleA.module);

            expect(runner.getModules()).toEqual([moduleA.module]);
            expect(runner.getModuleCount()).toBe(1);
        });
    });

    describe("registerMany", () => {
        it("should register multiple modules", () => {
            const moduleA = createModuleMock("A");
            const moduleB = createModuleMock("B");

            const result = runner.registerMany([moduleA.module, moduleB.module]);

            expect(result).toBe(runner);
            expect(runner.getModules()).toEqual([moduleA.module, moduleB.module]);
            expect(runner.getModuleCount()).toBe(2);
        });

        it("should deduplicate duplicate module instances", () => {
            const moduleA = createModuleMock("A");
            const moduleB = createModuleMock("B");

            runner.registerMany([moduleA.module, moduleB.module, moduleA.module]);

            expect(runner.getModules()).toEqual([moduleA.module, moduleB.module]);
            expect(runner.getModuleCount()).toBe(2);
        });
    });

    describe("mount", () => {
        it("should mount all registered modules in registration order", () => {
            const calls: string[] = [];
            const moduleA = createModuleMock("A", calls);
            const moduleB = createModuleMock("B", calls);

            runner.registerMany([moduleA.module, moduleB.module]);
            runner.mount();

            expect(moduleA.mount).toHaveBeenCalledOnce();
            expect(moduleB.mount).toHaveBeenCalledOnce();
            expect(moduleA.mount).toHaveBeenCalledWith(runner);
            expect(moduleB.mount).toHaveBeenCalledWith(runner);

            expect(calls).toEqual(["mount:A", "mount:B"]);
            expect(runner.isMounted()).toBe(true);
        });

        it("should do nothing if already mounted", () => {
            const calls: string[] = [];
            const moduleA = createModuleMock("A", calls);

            runner.register(moduleA.module);

            runner.mount();
            runner.mount();

            expect(moduleA.mount).toHaveBeenCalledTimes(1);
            expect(calls).toEqual(["mount:A"]);
            expect(runner.isMounted()).toBe(true);
        });

        it("should set mounted to true even when no modules are registered", () => {
            runner.mount();

            expect(runner.isMounted()).toBe(true);
            expect(runner.getModuleCount()).toBe(0);
        });
    });

    describe("destroy", () => {
        it("should destroy all registered modules in reverse order", () => {
            const calls: string[] = [];
            const moduleA = createModuleMock("A", calls);
            const moduleB = createModuleMock("B", calls);

            runner.registerMany([moduleA.module, moduleB.module]);
            runner.mount();
            runner.destroy();

            expect(moduleA.destroy).toHaveBeenCalledOnce();
            expect(moduleB.destroy).toHaveBeenCalledOnce();

            expect(calls).toEqual([
                "mount:A",
                "mount:B",
                "destroy:B",
                "destroy:A"
            ]);

            expect(runner.isMounted()).toBe(false);
        });

        it("should do nothing when runner is not mounted", () => {
            const calls: string[] = [];
            const moduleA = createModuleMock("A", calls);

            runner.register(moduleA.module);
            runner.destroy();

            expect(moduleA.destroy).not.toHaveBeenCalled();
            expect(calls).toEqual([]);
            expect(runner.isMounted()).toBe(false);
        });

        it("should allow mounting again after destroy", () => {
            const calls: string[] = [];
            const moduleA = createModuleMock("A", calls);

            runner.register(moduleA.module);

            runner.mount();
            runner.destroy();
            runner.mount();

            expect(moduleA.mount).toHaveBeenCalledTimes(2);
            expect(moduleA.destroy).toHaveBeenCalledTimes(1);

            expect(calls).toEqual([
                "mount:A",
                "destroy:A",
                "mount:A"
            ]);

            expect(runner.isMounted()).toBe(true);
        });
    });

    describe("state accessors", () => {
        it("should return false by default for isMounted", () => {
            expect(runner.isMounted()).toBe(false);
        });

        it("should return registered modules", () => {
            const moduleA = createModuleMock("A");
            const moduleB = createModuleMock("B");

            runner.registerMany([moduleA.module, moduleB.module]);

            expect(runner.getModules()).toEqual([moduleA.module, moduleB.module]);
        });

        it("should return the number of registered modules", () => {
            const moduleA = createModuleMock("A");
            const moduleB = createModuleMock("B");

            runner.registerMany([moduleA.module, moduleB.module]);

            expect(runner.getModuleCount()).toBe(2);
        });

        it("should return zero when no modules are registered", () => {
            expect(runner.getModuleCount()).toBe(0);
        });
    });
});