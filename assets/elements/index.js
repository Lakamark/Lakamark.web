/**
 * Initialize web comments for the application.
 *
 * After you can juste to import this file in your main script.
 *
 * @example : import './elements/index.js'
 *
 * After that, you have access to web components.
 */
import PuzzleCaptcha from "./PuzzleCaptcha.js";
import Spotlight from "./SpotlightElement/Spotlight.js";

customElements.define('puzzle-captcha', PuzzleCaptcha)
customElements.define('spotlight-bar', Spotlight)