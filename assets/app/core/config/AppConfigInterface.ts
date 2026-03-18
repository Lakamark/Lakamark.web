import {AppLanguage} from "./AppLanguage";

/**
 * Normalized frontend application configuration structure.
 *
 * This interface represents the validated and sanitized configuration
 * used to instantiate the AppConfig object.
 *
 * All values must be safe and normalized before reaching this structure.
 * It is typically produced by the config parser and validator layer.
 */
export interface AppConfigInterface {
    /** Unique user identifier or null if anonymous */
    userId: string | number | null;

    /** List of roles assigned to the current user */
    roles: string[];

    /** Indicates whether the user has a premium account */
    isPremium: boolean;

    /** Indicates whether the user is authenticated */
    isLogged: boolean;

    /** Preferred UI theme or null if not defined */
    preferredTheme: string | null;

    /** Current application language */
    language: AppLanguage;
}