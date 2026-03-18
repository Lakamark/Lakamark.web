import { beforeEach, describe, expect, it, vi } from "vitest";
import {FrontendKernel} from "../../../app/core/kernel/FrontendKernel";
import {AppRunner} from "../../../app/core/runner/AppRunner";
import {AppModuleInterface} from "../../../app/core/module/AppModuleInterface";
describe("FrontendKernel", () => {
    let runner: AppRunner;
    let kernel: FrontendKernel;

    const createModuleMock = (): AppModuleInterface => ({
        mount: vi.fn(),
        destroy: vi.fn(),
        isMounted: vi.fn(() => false)
    });

    beforeEach(() => {
        runner = new AppRunner();
        kernel = new FrontendKernel(runner);
    });

    describe("registerModule", () => {
        it("should register a single module", () => {
            const module = createModuleMock();
            const registerSpy = vi.spyOn(runner, "register");

            const result = kernel.registerModule(module);

            expect(registerSpy).toHaveBeenCalledOnce();
            expect(registerSpy).toHaveBeenCalledWith(module);
            expect(result).toBe(kernel);
        });

        it("should allow method chaining", () => {
            const moduleA = createModuleMock();
            const moduleB = createModuleMock();

            const result = kernel
                .registerModule(moduleA)
                .registerModule(moduleB);

            expect(result).toBe(kernel);
            expect(kernel.getModules()).toEqual([moduleA, moduleB]);
        });
    });

    describe("registerModules", () => {
        it("should register multiple modules", () => {
            const modules = [createModuleMock(), createModuleMock()];
            const registerManySpy = vi.spyOn(runner, "registerMany");

            const result = kernel.registerModules(modules);

            expect(registerManySpy).toHaveBeenCalledOnce();
            expect(registerManySpy).toHaveBeenCalledWith(modules);
            expect(result).toBe(kernel);
        });

        it("should allow method chaining", () => {
            const modules = [createModuleMock(), createModuleMock()];

            const result = kernel.registerModules(modules);

            expect(result).toBe(kernel);
        });
    });

    describe("boot", () => {
        it("should mount the runner", () => {
            const mountSpy = vi.spyOn(runner, "mount");

            kernel.boot();

            expect(mountSpy).toHaveBeenCalledOnce();
        });
    });

    describe("destroy", () => {
        it("should delegate cleanup to the runner", () => {
            const destroySpy = vi.spyOn(runner, "destroy");

            kernel.destroy();

            expect(destroySpy).toHaveBeenCalledOnce();
        });
    });

    describe("isBooted", () => {
        it("should return true when the runner is mounted", () => {
            const isMountedSpy = vi.spyOn(runner, "isMounted").mockReturnValue(true);

            const result = kernel.isBooted();

            expect(result).toBe(true);
            expect(isMountedSpy).toHaveBeenCalledOnce();
        });

        it("should return false when the runner is not mounted", () => {
            const isMountedSpy = vi.spyOn(runner, "isMounted").mockReturnValue(false);

            const result = kernel.isBooted();

            expect(result).toBe(false);
            expect(isMountedSpy).toHaveBeenCalledOnce();
        });
    });

    describe("getModules", () => {
        it("should return registered modules from the runner", () => {
            const modules = [createModuleMock(), createModuleMock()];
            const getModulesSpy = vi.spyOn(runner, "getModules").mockReturnValue(modules);

            const result = kernel.getModules();

            expect(result).toBe(modules);
            expect(getModulesSpy).toHaveBeenCalledOnce();
        });
    });
});