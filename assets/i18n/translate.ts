import {AppLanguage, DEFAULT_LANGUAGE} from "@core/config/AppLanguage";
import { appConfigParser } from "@core/config/AppConfigParser";

export type TranslationDictionary<T> = Record<AppLanguage, T>;

export function getCurrentLanguage(): AppLanguage {
    return appConfigParser().language ?? DEFAULT_LANGUAGE;
}

export function translate<T>(
    dictionary: TranslationDictionary<T>,
    language: AppLanguage = getCurrentLanguage()
): T {
    return dictionary[language] ?? dictionary[DEFAULT_LANGUAGE];
}