import {ThemeName, THEMES} from "../types";

/**
 * Check whether a value is a valid application theme.
 *
 * Useful when reading values from unsafe sources like:
 * - localStorage
 * - backend config
 * - DOM dataset
 */
export function isThemeName(value: unknown): value is ThemeName {
    return THEMES.includes(value as ThemeName);
}