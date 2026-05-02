import {describe, it, expect} from "vitest";
import {loadConfig} from "../../dom";
import {AppConfig} from "../../dom/contracts";

describe('loadConfig()', (): void => {
    it('loads config from DOM', () => {
        document.body.innerHTML = `
    <script id="lmk-config" type="application/json">
      {
        "userId": null,
        "roles": [],
        "isPremium": false,
        "isLogged": false,
        "environment": "dev",
        "preferredTheme": null,
        "language": "en"
      }
    </script>
  `;

        const config: AppConfig = loadConfig();

        expect(config.userId).toBeNull();
        expect(config.roles).toEqual([]);
        expect(config.isLogged).toBe(false);
        expect(config.language).toBe('en');
    });
});