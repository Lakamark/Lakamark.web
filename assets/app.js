import './css/app.scss';
import "./elements/index.js";

import * as Turbo from "@hotwired/turbo"
import {clearAllBodyScrollLocks, disableBodyScroll, enableBodyScroll} from 'body-scroll-lock'
import {throttle} from "./helpers/Timer.js";

document.addEventListener('turbo:load', () => {
    // Reset all scrollbar
    clearAllBodyScrollLocks()

    // Rebind click event for the hamburger button (Mobile scree only)
    initBtnHamburger();

})

/**
 * Toggle the menu
 */
function initBtnHamburger() {
    const btnHamburger = document.querySelector('#js-hamburger')
    const navBar = document.querySelector('.header__nav');
    if (btnHamburger) {
        let isOpen = false;
        btnHamburger.addEventListener('click', throttle(() => {
            document.querySelector('#main-header').classList.toggle('open-menu');
            isOpen ? enableBodyScroll(navBar) : disableBodyScroll(navBar);
            isOpen = !isOpen;
        }, 500))
    }
}

// Start Turbo
Turbo.start();