import {AppConfig} from "../../dom";

export function createFakeConfig(): AppConfig {
    return {
        userId: null,
        roles: [],
        isPremium: false,
        isLogged: false,
        preferredTheme: null,
        language: 'en',
        environment: "dev"
    };
}