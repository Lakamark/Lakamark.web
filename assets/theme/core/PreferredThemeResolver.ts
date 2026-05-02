import {AppContext} from "../../core";
import {isTheme, Theme} from "../types/Theme";
import {ThemeResolver} from "../Contracts";

/**
 * Resolves the initial theme from the application config.
 *
 * Falls back to `day` when the configured value is missing or invalid.
 */
export class PreferredThemeResolver implements ThemeResolver {
    resolve(context: AppContext): Theme {
        const preferredTheme= context.config.preferredTheme;

        return isTheme(preferredTheme) ? preferredTheme : 'day';
    }
}