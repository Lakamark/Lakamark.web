/**
 * Registers web components intended for the public-facing application only.
 *
 * These components are not used in the dashboard.
 *
 * Import this file in the public entry point.
 *
 * @example
 * import './elements/index.js';
 */
import PuzzleCaptcha from "./PuzzleCaptcha.js";
import Spotlight from "./SpotlightElement/Spotlight.js";
import ThemeComponent from "./ThemeComponent.ts";

customElements.define('puzzle-captcha', PuzzleCaptcha)
customElements.define('spotlight-bar', Spotlight)
customElements.define('theme-switcher', ThemeComponent)