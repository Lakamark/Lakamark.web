import {describe, it, expect, afterEach} from "vitest";
import {BodyClassThemeApplier} from "../../../theme";

describe("BodyClassThemeApplier", (): void => {
    afterEach((): void => {
        document.body.className = '';
    });

    it('applies the correct class to body', (): void => {
        const applier = new BodyClassThemeApplier();

        applier.apply('night');

        expect(document.body.classList.contains('night-theme')).toBe(true);
    });

    it('removes previous theme classes', (): void => {
        document.body.classList.add('day-theme');

        const applier = new BodyClassThemeApplier();
        applier.apply('night');

        expect(document.body.classList.contains('day-theme')).toBe(false);
        expect(document.body.classList.contains('night-theme')).toBe(true);
    });
});