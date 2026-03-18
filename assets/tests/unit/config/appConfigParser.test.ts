// @vitest-environment jsdom

import { beforeEach, describe, it, expect } from "vitest";
import {appConfigParser, resetAppConfigCache} from "../../../app/core/config/AppConfigParser";
import {AppConfig, AppConfigInterface} from "../../../app/core/config";

describe("AppConfigParser", () => {
    beforeEach(() => {
        resetAppConfigCache();
        document.body.innerHTML = "";
    });

    const validPayload = {
        userId: 123,
        roles: ["ROLE_USER"],
        isPremium: false,
        isLogged: true,
        preferredTheme: "dark",
        language: "en"
    } satisfies AppConfigInterface;

    const mountConfigScript = (payload: unknown): HTMLScriptElement => {
        const script = document.createElement("script");
        script.id = "lmk-config";
        script.type = "application/json";
        script.textContent = JSON.stringify(payload);

        document.body.appendChild(script);

        return script;
    };

    const mountRawConfigScript = (rawContent: string): HTMLScriptElement => {
        const script = document.createElement("script");
        script.id = "lmk-config";
        script.type = "application/json";
        script.textContent = rawContent;

        document.body.appendChild(script);

        return script;
    };

    const expectConfig = (
        config: AppConfig,
        expected: AppConfigInterface
    ): void => {
        expect(config.userId).toBe(expected.userId);
        expect(config.roles).toEqual(expected.roles);
        expect(config.isPremium).toBe(expected.isPremium);
        expect(config.isLogged).toBe(expected.isLogged);
        expect(config.preferredTheme).toBe(expected.preferredTheme);
        expect(config.language).toBe(expected.language);
    };

    describe("when the config is valid", () => {
        it("should parse the frontend config and return an AppConfig instance", () => {
            mountConfigScript(validPayload);

            const config = appConfigParser();

            expect(config).toBeInstanceOf(AppConfig);
            expectConfig(config, validPayload);
        });

        it("should support an anonymous user", () => {
            const payload = {
                userId: null,
                roles: [],
                isPremium: false,
                isLogged: false,
                preferredTheme: null,
                language: "fr"
            } satisfies AppConfigInterface;

            mountConfigScript(payload);

            const config = appConfigParser();

            expectConfig(config, payload);
        });

        it("should support a string user id", () => {
            const payload = {
                ...validPayload,
                userId: "user-123"
            } satisfies AppConfigInterface;

            mountConfigScript(payload);

            const config = appConfigParser();

            expectConfig(config, payload);
        });
    });

    describe("when normalization is needed", () => {
        it("should normalize invalid values into safe defaults", () => {
            mountConfigScript({
                userId: { invalid: true },
                roles: ["ROLE_USER", 42, null, "ROLE_ADMIN"],
                isPremium: 1,
                isLogged: 0,
                preferredTheme: "   ",
                language: "de"
            });

            const config = appConfigParser();

            expectConfig(config, {
                userId: null,
                roles: ["ROLE_USER", "ROLE_ADMIN"],
                isPremium: true,
                isLogged: false,
                preferredTheme: null,
                language: "en"
            });
        });
    });

    describe("when the DOM is invalid", () => {
        it("should throw if config script is missing", () => {
            expect(() => appConfigParser()).toThrow(
                'Frontend config script "#lmk-config" was not found or is not a <script> element.'
            );
        });

        it("should throw if the config element is not a script", () => {
            const div = document.createElement("div");
            div.id = "lmk-config";
            div.textContent = "{}";

            document.body.appendChild(div);

            expect(() => appConfigParser()).toThrow(
                'Frontend config script "#lmk-config" was not found or is not a <script> element.'
            );
        });

        it("should throw if the config script is empty", () => {
            mountRawConfigScript("   ");

            expect(() => appConfigParser()).toThrow(
                'Frontend config script "#lmk-config" is empty.'
            );
        });
    });

    describe("when the JSON payload is invalid", () => {
        it("should throw if the JSON is not readable", () => {
            mountRawConfigScript('{"userId": 999999999999999,');

            expect(() => appConfigParser()).toThrow(
                'Unable to parse frontend config from "#lmk-config".'
            );
        });

        it("should throw if the decoded payload is not an object", () => {
            mountRawConfigScript('"I turned myself into a pickle, Morty"');

            expect(() => appConfigParser()).toThrow(
                "Frontend config must be a JSON object."
            );
        });

        it("should throw if the decoded payload is null", () => {
            mountRawConfigScript("null");

            expect(() => appConfigParser()).toThrow(
                "Frontend config must be a JSON object."
            );
        });
    });

    describe("cache behavior", () => {
        it("should return the cached config instance on subsequent calls", () => {
            mountConfigScript(validPayload);

            const firstConfig = appConfigParser();
            const secondConfig = appConfigParser();

            expect(firstConfig).toBe(secondConfig);
        });

        it("should parse a fresh config after cache reset", () => {
            mountConfigScript(validPayload);

            const firstConfig = appConfigParser();

            resetAppConfigCache();
            document.body.innerHTML = "";

            const newPayload = {
                userId: 456,
                roles: ["ROLE_ADMIN"],
                isPremium: true,
                isLogged: true,
                preferredTheme: "light",
                language: "fr"
            } satisfies AppConfigInterface;

            mountConfigScript(newPayload);

            const secondConfig = appConfigParser();

            expect(firstConfig).not.toBe(secondConfig);
            expectConfig(secondConfig, newPayload);
        });
    });
});