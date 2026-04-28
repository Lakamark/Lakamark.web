/**
 * Supported application languages.
 */
export type AppLanguage = 'en' | 'fr';

/**
 * Supported  application.
 */
export type AppEnvironment = 'dev' | 'prod' | 'test';

/**
 * Application configuration injected from Twig.
 *
 * This object is serialized in the DOM via a JSON <script> tag
 * and parsed at runtime.
 */
export interface AppConfig {
    userId: number | null;
    roles: string[];
    isPremium: boolean;
    isLogged: boolean;
    preferredTheme: string | null;
    language: AppLanguage;
    environment: AppEnvironment;
}