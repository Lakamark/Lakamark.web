import {ThemeName} from "../types";

/**
 * Resolves an initial theme from an external source.
 */
export interface ThemeResolver {
    /**
     * Resolve the initial theme.
     */
    resolve(): ThemeName | null;
}