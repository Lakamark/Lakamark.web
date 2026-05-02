import {describe, it, expect} from "vitest";
import {AppRunner} from "../../core";
import {createApp} from "../../core/application";

describe('createApp', () => {
    it('returns an AppRunner instance', () => {
        const app: AppRunner = createApp();

        expect(app).toBeInstanceOf(AppRunner);
    });
});