import { describe, it, expect } from 'vitest';
import {AppConfig, AppConfigInterface} from "../../../app/core/config";

describe('AppConfig', () => {

    const baseConfig = {
        userId: 123,
        roles: ['ROLE_USER'],
        isPremium: false,
        isLogged: true,
        preferredTheme: "dark",
        language: 'en'
    } satisfies AppConfigInterface;

    const createConfig = (
        overrides: Partial<AppConfigInterface> = {}
    ): AppConfig => {
        return new AppConfig({
            ...baseConfig,
            ...overrides
        });
    };

    it('should  hydrate all properties correctly', () => {
        const config = createConfig();

        expect(config.userId).toBe(123)
        expect(config.roles).toEqual(['ROLE_USER']);
        expect(config.isPremium).toBe(false);
        expect(config.isLogged).toBe(true);
        expect(config.preferredTheme).toBe('dark');
        expect(config.language).toBe('en');
    });

    it('should returns true when user has given role', () => {
        const config = createConfig({
            roles: ['ROLE_USER', 'ROLE_VALIDATED_USER','ROLE_ADMIN']
        })

        expect(config.hasRole('ROLE_ADMIN')).toBe(true);
    });

    it('should returns false when user does not have the given role', () => {
        const config = createConfig();

        expect(config.hasRole('ROLE_ADMIN')).toBe(false);
    });

    it('should returns true when the user is authenticated', () => {
        const config = createConfig({
            isLogged: true
        })

        expect(config.isAuthenticated()).toBe(true);
    });

    it('should returns false when the user is not authenticated', () => {
        const config = createConfig({
            isLogged: false
        })

        expect(config.isAuthenticated()).toBe(false);
    });

    it('should supports an anonymous user', () => {
        const config = createConfig({
            userId: null,
            roles: [],
            isLogged: false
        });

        expect(config.userId).toBeNull();
        expect(config.roles).toEqual([]);
        expect(config.hasRole('ROLE_USER')).toBe(false);
        expect(config.isAuthenticated()).toBe(false);
    });

    it('should support an userId', () => {
        const config = createConfig({
            userId: 'user-123'
        });

        expect(config.userId).toBe('user-123');
    });

    it('should supports a null preferredTheme', () => {
        const config = createConfig({
            preferredTheme: null
        });

        expect(config.preferredTheme).toBe(null);
    });
});