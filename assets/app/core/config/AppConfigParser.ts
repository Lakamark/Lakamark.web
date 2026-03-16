import {AppConfig} from "@core/config/AppConfig";
import {AppConfigInterface} from "@core/config/AppConfigInterface";
import {normalizeLanguage} from "@core/config/AppLanguage";

const CONFIG_ELEMENT_ID  = "lmk-config";

let appConfigCache: AppConfig | null = null;

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

export function resetAppConfigCache(): void {
    appConfigCache = null;
}

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

function normalizeUserId(value: unknown): string | number | null {
    if (value === null || typeof value === "string" || typeof value === "number") {
        return value;
    }

    return null;
}

function normalizeRoles(value: unknown): string[] {
    if (!Array.isArray(value)) {
        return [];
    }

    return value.filter((item): item is string => typeof item === "string");
}

function normalizeNullableString(value: unknown): string | null {
    return typeof value === "string" && value.trim() !== "" ? value : null;
}

function isRecord(value: unknown): value is Record<string, unknown> {
    return typeof value === "object" && value !== null;
}