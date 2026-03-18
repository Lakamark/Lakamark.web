/**
 * List of supported application languages.
 *
 * This constant defines all valid language identifiers accepted by the frontend.
 * It is used as the single source of truth for language validation.
 */
export const APP_LANGUAGES = ["en", "fr"] as const;

/**
 * Union type representing all supported application languages.
 */
export type AppLanguage = typeof APP_LANGUAGES[number];

/**
 * Default application language used as a fallback when no valid language is provided.
 */
export const DEFAULT_LANGUAGE: AppLanguage = "en";

/**
 * Type guard to check if a value is a valid AppLanguage.
 *
 * @param value - Unknown value to validate
 *
 * @returns `true` if the value is a supported language, otherwise `false`
 */
export function isAppLanguage(value: unknown): value is AppLanguage {
    return typeof value === "string" && APP_LANGUAGES.includes(value as AppLanguage);
}

/**
 * Normalizes a language value into a valid AppLanguage.
 *
 * If the provided value is not a supported language,
 * the default language is returned instead.
 *
 * This ensures the application always operates with a valid language.
 *
 * @param value - Raw language value (e.g. from backend or user input)
 *
 * @returns A valid AppLanguage
 */
export function normalizeLanguage(value: unknown): AppLanguage {
    return isAppLanguage(value) ? value : DEFAULT_LANGUAGE;
}