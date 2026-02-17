import './css/app.scss';

import * as Turbo from "@hotwired/turbo"
import {HeaderUI} from "./lib/HeaderUI.js";

let headerInstance = null;

/**
 * Re-init header after every Turbo visit
 */
function initHeader() {
    headerInstance?.destroy();
    headerInstance = new HeaderUI().init();
}

/**
 * Cleanup before Turbo caches the page
 */
function destroyHeader() {
    headerInstance?.destroy();
    headerInstance = null;
}

document.addEventListener('turbo:load', initHeader);
document.addEventListener('turbo:before-cache', destroyHeader);

// start Turbo
Turbo.start()