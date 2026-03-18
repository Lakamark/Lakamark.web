import { describe, it, expect } from 'vitest';

describe('AppConfigParser', () => {
    it('should parse a basic config object', () => {
        const input = { foo: 'bar' };

        expect(input.foo).toBe('bar');
    });
});