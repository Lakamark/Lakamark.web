import {
    AppConfig,
    normalizeLanguage,
    AppConfigInterface
} from "./index";

const CONFIG_ELEMENT_ID  = "lmk-config";

let appConfigCache: AppConfig | null = null;

/**
 * Validates and normalizes the raw frontend configuration payload.
 *
 * This function acts as a strict contract boundary between the backend
 * and the frontend runtime.
 *
 * It ensures that:
 * - the decoded JSON payload has the expected shape
 * - all required fields are normalized into safe, predictable values
 * - no invalid or unexpected types propagate into the application core
 *
 * This function is executed BEFORE the FrontendKernel boot process.
 * Any invalid configuration MUST be rejected here to prevent inconsistent
 * application state.
 *
 *
 * @returns A normalized configuration object compliant with AppConfigInterface.
 *
 * @throws {Error} If the payload is not a valid configuration object.
 * @param doc
 */
export function appConfigParser(doc: Document = document): AppConfig {
    // read from the cache
    if (appConfigCache) {
        return appConfigCache;
    }

    const element = doc.getElementById(CONFIG_ELEMENT_ID);

    if (!(element instanceof HTMLScriptElement)) {
        throw new Error(
            `Frontend config script "#${CONFIG_ELEMENT_ID}" was not found or is not a <script> element.`
        );
    }

    const rawJson = element.textContent?.trim();

    if (!rawJson) {
        throw new Error(
            `Frontend config script "#${CONFIG_ELEMENT_ID}" is empty.`
        );
    }

    let parsedContent: unknown;

    try {
        parsedContent = JSON.parse(rawJson);
    } catch (error) {
        throw new Error(
            `Unable to parse frontend config from "#${CONFIG_ELEMENT_ID}".`,
            { cause: error }
        );
    }

    appConfigCache = new AppConfig(validateAppConfig(parsedContent));

    return appConfigCache;
}

/**
 * Clears the in-memory application config cache.
 *
 * This is mainly useful in tests or in environments where the config
 * script content may change between runs and a fresh parse is required.
 */
export function resetAppConfigCache(): void {
    appConfigCache = null;
}

/**
 * Validates the raw decoded JSON value and converts it into a normalized
 * `AppConfigInterface` structure expected by `AppConfig`.
 *
 * @param value - Raw decoded JSON value.
 *
 * @returns A normalized config object safe to pass to `AppConfig`.
 *
 * @throws {Error} When the decoded JSON payload is not a plain object.
 */
function validateAppConfig(value: unknown): AppConfigInterface {
    if (!isRecord(value)) {
        throw new Error("Frontend config must be a JSON object.");
    }

    return {
        userId: normalizeUserId(value.userId),
        roles: normalizeRoles(value.roles),
        isPremium: Boolean(value.isPremium),
        isLogged: Boolean(value.isLogged),
        preferredTheme: normalizeNullableString(value.preferredTheme),
        language: normalizeLanguage(value.language)
    }
}

/**
 * Normalizes the user identifier value.
 *
 * Accepts only `null`, `string` or `number`.
 * Any other value is converted to `null`.
 */
function normalizeUserId(value: unknown): string | number | null {
    if (value === null || typeof value === "string" || typeof value === "number") {
        return value;
    }

    return null;
}

/**
 * Normalizes the roles collection into a string array.
 *
 * Non-array values return an empty array.
 * Non-string items are discarded.
 */
function normalizeRoles(value: unknown): string[] {
    if (!Array.isArray(value)) {
        return [];
    }

    return value.filter((item): item is string => typeof item === "string");
}

/**
 * Returns a non-empty string value or `null`.
 */
function normalizeNullableString(value: unknown): string | null {
    return typeof value === "string" && value.trim() !== "" ? value : null;
}

/**
 * Checks whether a value is a non-null object record.
 */
function isRecord(value: unknown): value is Record<string, unknown> {
    return typeof value === "object" && value !== null;
}