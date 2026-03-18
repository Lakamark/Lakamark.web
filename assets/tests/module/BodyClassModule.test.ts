import {beforeEach, describe, expect, it} from "vitest";
import {AppRunner} from "../../app/core/runner/AppRunner";
import {BodyClassModule} from "../stub/BodyClassModule";

describe("BodyClassModule", () => {
    let runner: AppRunner;

    beforeEach(() => {
        document.body.className = "";
        runner = new AppRunner();
    });

    it('should add the class to the body on mount', () => {
        const module = new BodyClassModule("theme-dark");

        module.mount(runner);

        expect(document.body.classList.contains("theme-dark")).toBe(true)
    });

    it('should remove the class from the body on destroy', () => {
        const module = new BodyClassModule("theme-dark");

        module.mount(runner);
        module.destroy();

        expect(document.body.classList.contains("theme-dark")).toBe(false);
        expect(module.isMounted()).toBe(false);
    });

    it("should not mount twice", () => {
        const module = new BodyClassModule("theme-dark");

        module.mount(runner);
        module.mount(runner);

        expect(document.body.className).toBe("theme-dark");
        expect(module.isMounted()).toBe(true);
    });

    it("should do nothing on destroy when not mounted", () => {
        const module = new BodyClassModule("theme-dark");

        module.destroy();

        expect(document.body.classList.contains("theme-dark")).toBe(false);
        expect(module.isMounted()).toBe(false);
    });

    describe("BodyClassModule integration with AppRunner", () => {
        beforeEach(() => {
            document.body.className = "";
        });

        it("should mount and destroy through the runner lifecycle", () => {
            const runner = new AppRunner();
            const module = new BodyClassModule("theme-dark");

            runner.register(module);

            runner.mount();
            expect(document.body.classList.contains("theme-dark")).toBe(true);
            expect(module.isMounted()).toBe(true);

            runner.destroy();
            expect(document.body.classList.contains("theme-dark")).toBe(false);
            expect(module.isMounted()).toBe(false);
        });
    });
});