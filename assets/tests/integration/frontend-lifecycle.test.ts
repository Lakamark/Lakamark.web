import {beforeEach, describe, it, expect} from "vitest";
import {FrontendKernel} from "../../app/core/kernel/FrontendKernel";
import {BodyClassModule} from "../stub/BodyClassModule";

describe("Full frontend lifecycle", () => {
    beforeEach(() => {
        document.body.className = "";
    });

    it("should run the full lifecycle through the kernel", () => {
        const kernel = new FrontendKernel();
        const module = new BodyClassModule("theme-dark");

        kernel.registerModule(module);

        kernel.boot();

        expect(document.body.classList.contains("theme-dark")).toBe(true);
        expect(module.isMounted()).toBe(true);
        expect(kernel.isBooted()).toBe(true);

        kernel.destroy();

        expect(document.body.classList.contains("theme-dark")).toBe(false);
        expect(module.isMounted()).toBe(false);
        expect(kernel.isBooted()).toBe(false);
    });
});