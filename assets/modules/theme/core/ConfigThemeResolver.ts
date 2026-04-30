import {ThemeResolver} from "../Contracts";
import {AppConfig} from "../../../dom";
import {ThemeName} from "../types";
import {isThemeName} from "./themeGuards";

/**
 * Resolves the initial theme from AppConfig.
 *
 * This is the bridge between backend (Twig) and frontend.
 */
export class ConfigThemeResolver implements ThemeResolver {
    constructor(private readonly config: AppConfig) {}

    /**
     * Resolve theme from config.preferredTheme.
     */
    resolve(): ThemeName | null {
        const value: string|null = this.config.preferredTheme;

        return isThemeName(value) ? value : null;
    }
}