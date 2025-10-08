/**
 * Use this script to add your feature for the dashboard section
 */

import './css/dashboard.scss';
import './elements/index.js'
import {disableBodyScroll, enableBodyScroll} from "body-scroll-lock";


// Sidebar trigger
const sidebarTrigger = document.getElementById('js-toggle-menu');
const sidebar = document.getElementById('js-sidebar-collapse');

sidebarTrigger.addEventListener('click', () => {
    let isOpen = false;
    let bodyContent = document.querySelector('body');
    bodyContent.classList.toggle('sidebar-open');
    isOpen ? enableBodyScroll(bodyContent) : disableBodyScroll(bodyContent);
});

// Sidebar collapse
const sidebarCollapse = () => {
    document.body.classList.toggle('sidebar-collapsed');
}

sidebar.addEventListener('click', sidebarCollapse)



// Theme switcher
const switcherTheme = () => {
    // Get the root element and data-theme value
    const rootElement = document.documentElement;
    let dataTheme = rootElement.getAttribute('data-theme'), newTheme

    newTheme = (dataTheme === 'light' ? 'dark' : 'light');
    rootElement.setAttribute('data-theme', newTheme);

    // Set the new local storage item
    localStorage.setItem('theme', newTheme);
}

// Add eventListener
document.querySelector('#js-sidebar-switcher').addEventListener('click', switcherTheme);