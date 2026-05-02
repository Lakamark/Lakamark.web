import {describe, it, expect} from "vitest";
import {AppRunner} from "../../core";
import {createApp} from "../../application";

describe('createApp', () => {
    it('returns an AppRunner instance', () => {
        const app: AppRunner = createApp({
            userId: null,
            roles: [],
            isPremium: false,
            isLogged: false,
            environment: 'test',
            preferredTheme: null,
            language: 'en',
        });

        expect(app).toBeInstanceOf(AppRunner);
    });
});