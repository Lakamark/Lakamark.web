import {describe, it, expect} from "vitest";
import {
    getAllThemeClassNames,
    getThemeClassName,
    isTheme
} from "../../theme";

describe("ThemeType", (): void => {
    it('recognizes supported themes', (): void => {
        expect(isTheme('day')).toBe(true);
        expect(isTheme('night')).toBe(true);
        expect(isTheme('invalid')).toBe(false);
    });

    it('returns the theme CSS class name', (): void => {
        expect(getThemeClassName('day')).toBe('day-theme');
        expect(getThemeClassName('night')).toBe('night-theme');
    });

    it('returns all theme CSS class names', (): void => {
        expect(getAllThemeClassNames()).toEqual(['day-theme', 'night-theme']);
    });
});