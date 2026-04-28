import type {
    AppConfig,
    AppEnvironment,
    AppLanguage
} from './contracts';
import {queryRequired} from "./query";
import {assertAppConfigShape} from "./configValidator";

const CONFIG_SELECTOR = '#lmk-config';
const DEFAULT_LANGUAGE: AppLanguage = 'en';
const DEFAULT_ENV: AppEnvironment = 'prod';

const SUPPORTED_LANGUAGES: AppLanguage[] = ['en', 'fr'];
const SUPPORTED_ENVS: AppEnvironment[] = ['dev', 'prod', 'test'];

/**
 * Normalizes a language value to a supported language.
 *
 * @param value - Raw language value
 * @returns A valid AppLanguage
 */
export function normalizeLanguage(value: unknown): AppLanguage {
    if (typeof value !== 'string') {
        return DEFAULT_LANGUAGE;
    }

    return SUPPORTED_LANGUAGES.includes(value as AppLanguage)
        ? (value as AppLanguage)
        : DEFAULT_LANGUAGE;
}

/**
 * Normalizes an environment value to a supported application environment.
 *
 * @param value - Raw environment value
 * @returns A valid AppEnvironment
 */
export function normalizeEnvironment(value: unknown): AppEnvironment {
    if (typeof value !== 'string') {
        return DEFAULT_ENV;
    }

    return SUPPORTED_ENVS.includes(value as AppEnvironment)
        ? (value as AppEnvironment)
        : DEFAULT_ENV;
}

/**
 * Reads and parses the application configuration from the DOM.
 *
 * The configuration is expected to be injected via a <script> tag:
 * <script id="lmk-config" type="application/json">...</script>
 *
 * @param documentRef - Document reference (useful for testing)
 * @returns Parsed and validated AppConfig object
 *
 * @throws {Error} If the config element is missing or empty
 */
export function readAppConfig(documentRef: Document = document): AppConfig {
    const script = queryRequired<HTMLScriptElement>(documentRef, CONFIG_SELECTOR);
    const raw = script.textContent?.trim();

    if (!raw) {
        throw new Error('App config script is empty.');
    }

    const parsed = JSON.parse(raw) as Partial<AppConfig>;

    assertAppConfigShape(parsed);

    return {
        userId: typeof parsed.userId === 'number' ? parsed.userId : null,
        roles: Array.isArray(parsed.roles) ? parsed.roles.filter(isString) : [],
        isPremium: parsed.isPremium === true,
        isLogged: parsed.isLogged === true,
        preferredTheme:
            typeof parsed.preferredTheme === 'string'
                ? parsed.preferredTheme
                : null,
        language: normalizeLanguage(parsed.language),
        environment: normalizeEnvironment(parsed.environment)
    };
}


/**
 * Type guard to ensure a value is a string.
 *
 * @param value - Unknown value
 * @returns True if value is a string
 */
export function isString(value: unknown): value is string {
    return typeof value === 'string';
}