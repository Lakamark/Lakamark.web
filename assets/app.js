import './css/app.scss';
import './elements/index.js'

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

document.addEventListener('turbo:load', () => {
    // If window.LmkConfig can change between pages, do this first:
    initHeader()



});
document.addEventListener('turbo:before-cache', () => {
    destroyHeader()
    // reset the lmk config
});

// start Turbo
Turbo.start()